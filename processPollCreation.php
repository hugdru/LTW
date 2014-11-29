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
    header('Location: poll.php?create=errorCsrf');
    exit();
}

if ( (!isset(
    $_POST['name'], $_POST['visibility'], $_POST['synopsis']
    , $_POST['description'], $_POST['option'], $_FILES)
     )
) {
    header('location: poll.php?create=errorData');
    exit();
}

$error = null;
// Check validity of values with regex
$name = $_POST['name'];
if (!preg_match('/^[A-Z][\s\w]{5,60}$/', $name)) {
    $error = 'Name';
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

// Same
$description = $_POST['description'];

// Same
$options = $_POST['option'];

// Check if the image is really one
$image = $_FILES['image']['tmp_name'];
if ($image !== '') {
    $imageInfo = getimagesize($image);
    if (!$isImage) {
        $error .= 'Image';
    } else {
        $imageFileName = basename($_FILES['image']['name']);
    }
}
if ($error !== null) {
    header("Location: poll.php?create=error$error");
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
    (name, dateCreation, synopsis, conclusion, generatedKey, image, idUser, idState, idVisibility)
    VALUES (:name, :dateCreation, :synopsis, null, :generatedKey, null, :idUser, :idState, :idVisibility)"
);
try {
    $stmt->execute(
        array(
            ':name' => $name,
            ':dateCreation' => date('Y-m-d'),
            ':synopsis' => $synopsis,
            ':generatedKey' => $generatedKey,
            ':idUser' => $_SESSION['idUser'],
            ':idState' => $idState,
            ':idVisibility' => $idVisibility
        )
    );
} catch (PDOException $e) {
    switch ($e->errorInfo[0]) {
    case '23000':
        header('Location: poll.php?create=errorDuplicate');
        exit();
    default:
        die('Unexpected database error');
    }
}

if (!($pollId = $dbh->lastInsertId())) {
    header('Location: poll.php?create=errorInsert');
    exit();
}

// Insert the Questions in the database
$stmt = $dbh->prepare(
    'INSERT INTO Question (options, description, idPoll)
    VALUES (:option, :description, :idPoll)'
);
$i = 0;
foreach ($options as $option) {
    if ($description[$i] === '') {
        $description[$i] = null;
    }
    $jsonOption = json_encode($option);
    $stmt->bindParam(':option', $jsonOption);
    $stmt->bindParam(':description', $description[$i]);
    $stmt->bindParam(':idPoll', $pollId);
    if (!$stmt->execute()) {
        $dbh->rollBack();
        header('Location: poll.php?create=errorInsert');
        exit();
    }
}

if ($image !== '') {
    // Put image in the filesystem
    $imagePath = "images/$userId/$pollId/";

    if (mkdir($imagePath, 0744, true)) {
        // Put the image name in the database
        $dbh->prepare(
            'UPDATE Poll
            SET image = :imageFileName
            WHERE idPoll = :pollId'
        );
        if (!$stmt->execute(
            array(
                ':imageFileName' => $imageFileName,
                ':idPoll' => $pollId
            )
        )) {
            goto cleanup;
        }
    } else {
        cleanup:
        $dbh->rollBack();
        header('Location: poll.php?create=errorInsert');
        exit();
    }
    // Place file in disk
    $file = fopen($imagePath + $imageFileName, 'w');
    if (!fwrite($file, $image)) {
        $dbh->rollBack();
        rmdir($imagePath);
        header('Location: poll.php?create=errorFile');
        exit();
    }
    fclose($file);
}

if (isset($generatedKey)) {
    $pollId = $generatedKey;
}

$dbh->commit();

header("Location: poll.php?{$_POST['visibility']}=$pollId");
exit();
?>
