<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'codeIncludes/databasePipe.php';
require_once 'functions/validLogin.php';
require_once 'functions/base64UrlSafe.php';

if (!validLogin()) {
    header('Location: index.php?error=login');
    exit();
}

if ($_POST['csrf'] !== $_SESSION['csrf_token']) {
    header('Location: pollCreate.php?err=Csrf');
    exit();
}

if ( (!isset(
    $_POST['name'], $_POST['visibility'], $_POST['synopsis']
    , $_POST['description'], $_POST['option'], $_FILES)
     )
) {
    header('location: pollCreate.php?err=Data');
    exit();
}

$error = null;
// Check validity of values with regex

$nameLength = strlen($_POST['name']);
$name = $_POST['name'];
if ($name === '') {
    $error = 'nameEmpty';
} else if ($nameLength < 5) {
    $error = 'nameShort';
} else if ($nameLength > 100) {
    $error = 'nameLong';
} else if (!preg_match('/^[^0-9]{5}/', $name)) {
    $error = 'nameFirst5NoNumber';
}

if ($_POST['visibility'] !== '') {
    $stmt = $dbh->prepare(
        'SELECT idVisibility FROM Visibility WHERE name LIKE :visibility'
    );
    $stmt->bindParam(':visibility', $_POST['visibility']);
    $stmt->execute();
    $idVisibility = $stmt->fetch();
    if (!$idVisibility) {
        $error .= 'Visibility';
    }
    $idVisibility = $idVisibility['idVisibility'];
} else {
    $error .= 'Visibility';
}

// I see no reason to check this, when we echo it to
// html we should use something like htmlentities.
$synopsis = $_POST['synopsis'];
if ($synopsis === '') {
    $synopsis = null;
}

// Malicious user could send "array" with different indexes
$i = 0;
$descriptions = [];
foreach ($_POST['description'] as $desc) {
    if ($desc === '') {
        header('location: pollCreate.php?err=invalidDescription');
        exit();
    }
    $descriptions[$i] = $desc;
    ++$i;
}

// Same
$options = [];
$i = 0;
foreach ($_POST['option'] as $option) {

    if (count($option) < 2) {
        header('Location: pollCreate.php?err=MissingOptions');
        exit();
    }

    $t = 0;
    foreach ($option as $radio) {
        if ($radio === '') {
            header('location: pollCreate.php?err=invalidRadio');
            exit();
        }
        $options[$i][$t] = $radio;
        ++$t;
    }
    ++$i;
}

// Check if the image is really one
$image = $_FILES['image']['tmp_name'];
$imageFileName = null;

if ($image !== '') {
    $imageInfo = getimagesize($image);
    if (!$imageInfo) {
        $error .= 'NotImage';
    } else if ($_FILES['image']['size'] > 10000000) {
        $error .= 'ImageSize';
    } else {
        $imageFileName = basename($_FILES['image']['name']);
    }
}
if ($error !== null) {
    header("Location: pollCreate.php?err=$error");
    exit();
}

// Put everything in database
$stmt = $dbh->query(
    'SELECT idState FROM State WHERE name LIKE \'open\''
);
$idState = $stmt->fetch();
if (!$idState) {
    die('There is no such State');
}
$idState = $idState['idState'];

$generatedKey = null;
if (preg_match('/^private$/i', $_POST['visibility'])) {
    $generatedKey = uniqueUrlBase64Encode(sha1(uniqid($_SESSION['idUser'], true), true));
}

$dbh->beginTransaction();
$stmt = $dbh->prepare(
    "INSERT INTO Poll
    (name, dateCreation, timeCreation, synopsis, conclusion, generatedKey, image, idUser, idState, idVisibility)
    VALUES (:name, :dateCreation, :timeCreation, :synopsis, null, :generatedKey, :image, :idUser, :idState, :idVisibility)"
);
try {
    $stmt->execute(
        array(
            ':name' => $name,
            ':dateCreation' => gmdate('Y-m-d'),
            ':timeCreation' => gmdate('H:i:s'),
            ':synopsis' => $synopsis,
            ':generatedKey' => $generatedKey,
            ':image' => $imageFileName,
            ':idUser' => $_SESSION['idUser'],
            ':idState' => $idState,
            ':idVisibility' => $idVisibility
        )
    );
} catch (PDOException $e) {
    switch ($e->errorInfo[0]) {
    case '23000':
        header('Location: pollCreate.php?err=Duplicate');
        exit();
    default:
        die('Unexpected database error');
    }
}

if (!($pollId = $dbh->lastInsertId())) {
    $dbh->rollBack();
    header('Location: pollCreate.php?err=Insert');
    exit();
}

// Insert the Questions in the database
$stmt = $dbh->prepare(
    'INSERT INTO Question (options, result, description, idPoll)
    VALUES (:option, :result, :description, :idPoll)'
);
foreach ($options as $key =>$option) {
    $jsonOption = json_encode($option);
    $jsonResult = json_encode(array_fill(0, count($option), 0));
    $stmt->bindParam(':option', $jsonOption);
    $stmt->bindValue(':result', $jsonResult);
    $stmt->bindParam(':description', $descriptions[$key]);
    $stmt->bindParam(':idPoll', $pollId);
    if (!$stmt->execute()) {
        $dbh->rollBack();
        header('Location: pollCreate.php?err=Insert');
        exit();
    }
}

// Place file in disk
if ($image !== '') {
    $imagePath = "images/{$_SESSION['idUser']}/$pollId/";
    if (!is_dir($imagePath)) {
        mkdir($imagePath, 0744, true);
    }

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath . $imageFileName)) {
        $dbh->rollBack();
        header('Location: pollCreate.php?err=File');
        exit();
    }
}

if (isset($generatedKey)) {
    $pollId = $generatedKey;
}

$dbh->commit();

header("Location: poll.php?{$_POST['visibility']}=$pollId");
exit();
?>
