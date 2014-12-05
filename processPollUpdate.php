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
    header("Location: index.php?err=csrf");
    exit();
}

if ( (!isset(
    $_POST['name'], $_POST['visibility'], $_POST['synopsis']
    , $_POST['description'], $_POST['option'], $_POST['pollId'], $_POST['mode'], $_FILES)
     )
) {
    header("Location: index.php?err=data");
    exit();
}

$mode = $_POST['mode'];
$pollId = $_POST['pollId'];

$mode = mb_strtolower($mode);

$error = null;

// Check validity of values with regex

$nameLength = strlen($_POST['name']);

if ($_POST['name'] === '') {
    $error = 'nameEmpty';
} else if ($nameLength < 5) {
    $error = 'nameShort';
} else if ($nameLength > 100) {
    $error = 'nameLong';
} else if (!preg_match('/^[^0-9]{5}/', $_POST['name'])) {
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

if ($mode === 'private') {
    // Check if private poll exists
    $stmt = $dbh->prepare(
        'SELECT * FROM Poll
        WHERE generatedKey = :generatedKey'
    );
    $stmt->bindParam(':generatedKey', $pollId);
} else if ($mode === 'public') {
    // Check if public poll exists and is really public
    $stmt = $dbh->prepare(
        'SELECT * FROM Poll
        WHERE
            idPoll = :idPoll AND
            idVisibility = (SELECT idVisibility FROM Visibility WHERE name LIKE \'public\')'
    );
    $stmt->bindParam(':idPoll', $pollId);
} else {
    header("Location: index.php?err=mode");
    exit();
}

$stmt->execute();
$pollQuery = $stmt->fetch();
if (!$pollQuery) {
    header("Location: index.php?err=noId");
    exit();
}

if ($error !== null) {
    header("Location: poll.php?$mode=$pollId&edit&err=$error");
    exit();
}


if ($pollQuery['idUser'] != $_SESSION['idUser']) {
    header("Location: poll.php?$mode=$pollId&edit&err=notOwner");
    exit();
}

if ($image === '') {
    $imageFileName = $pollQuery['image'];
}

// Get Questions From database
$stmt = $dbh->prepare(
    'SELECT * FROM Question
    WHERE idPoll = :idPoll'
);

$stmt->bindParam(':idPoll', $pollQuery['idPoll']);
$stmt->execute();
if (!($questionsQuery = $stmt->fetchAll())) {
    header("Location: poll.php?$mode=$pollId&edit&err=noQuestions");
    exit();
}

// Check if radios changed and if so reset UserQuestionAnswer and
// UnauthenticatedQuestionAnswer

$resetAnswer = false;

if (count($_POST['option']) !== count($questionsQuery)) {
    $resetAnswer = true;
    foreach ($_POST['option'] as $postOptions) {
        if (count($postOptions) < 2) {
            header("Location: poll.php?$mode=$pollId&edit&err=notEnoughOptions");
            exit();
        }
    }
} else {
    foreach ($questionsQuery as $keyQuestion => $questionQuery) {

        if ($questionQuery['description'] !== $_POST['description'][$keyQuestion]) {
            $resetAnswer = true;
            break;
        }

        $decodedRadios = json_decode($questionQuery['options']);

        $postOptionsLength = count($_POST['option'][$keyQuestion]);
        if ($postOptionsLength < 2) {
            header("Location: poll.php?$mode=$pollId&edit&err=notEnoughOptions");
            exit();
        }

        if (count($decodedRadios) !==  $postOptionsLength) {
            $resetAnswer = true;
            break;
        }

        foreach ($decodedRadios as $keyRadio => $decodedRadio) {
            if ($decodedRadio != $_POST['option'][$keyQuestion][$keyRadio]) {
                $resetAnswer = true;
                break;
            }
        }

        if ($resetAnswer === true) {
            break;
        }

    }
}

$generatedKey = $pollQuery['generatedKey'];

if ($idVisibility !== $pollQuery['idVisibility'] && $mode === 'public') {
    $generatedKey = uniqueUrlBase64Encode(sha1(uniqid($_SESSION['idUser'], true), true));
} else if ($idVisibility !== $pollQuery['idVisibility'] && $mode === 'private') {
    $generatedKey = null;
}

if ($resetAnswer === true) {
    $date = date('Y-m-d');
} else {
    $date = $pollQuery['dateCreation'];
}

$dbh->beginTransaction();
$stmt = $dbh->prepare(
    'UPDATE Poll
    SET name = :name, dateCreation = :dateCreation, synopsis = :synopsis, generatedKey = :generatedKey, image = :image, idVisibility = :idVisibility
    WHERE idPoll = :idPoll'
);
try {
    if (!$stmt->execute(
        array(
            ':name' => $_POST['name'],
            ':dateCreation' => $date,
            ':synopsis' => $_POST['synopsis'],
            ':generatedKey' => $generatedKey,
            ':image' => $imageFileName,
            ':idVisibility' => $idVisibility,
            ':idPoll' => $pollQuery['idPoll']
        )
    )) {
        $dbh->rollBack();
        header("Location: poll.php?$mode=$pollId&edit&err=update");
        exit();
    }
} catch (PDOException $e) {
    switch ($e->errorInfo[0]) {
    case '23000':
        header("Location: poll.php?$mode=$pollId&edit&err=duplicate");
        $dbh->rollBack();
        exit();
    default:
        $dbh->rollBack();
        die('Unexpected database error');
    }
}

if ($resetAnswer) {

    // Delete authenticated user answers
    $stmt = $dbh->prepare(
        'DELETE FROM UserQuestionAnswer
        WHERE idQuestion IN (SELECT idQuestion FROM Question WHERE idPoll = :idPoll)'
    );
    $stmt->bindParam(':idPoll', $pollQuery['idPoll']);
    if (!$stmt->execute()) {
        $dbh->rollBack();
        header("Location: poll.php?$mode=$pollId&edit&err=delete");
        exit();
    }

    // Delete unauthenticated user answers
    $stmt = $dbh->prepare(
        'DELETE FROM UnauthenticatedQuestionAnswer
        WHERE idQuestion IN (SELECT idQuestion FROM Question WHERE idPoll = :idPoll)'
    );
    $stmt->bindParam(':idPoll', $pollQuery['idPoll']);
    if (!$stmt->execute()) {
        $dbh->rollBack();
        header("Location: poll.php?$mode=$pollId&edit&err=delete");
        exit();
    }

    // Delete questions
    $stmt = $dbh->prepare(
        'DELETE FROM Question
        WHERE idPoll = :idPoll'
    );
    $stmt->bindParam(':idPoll', $pollQuery['idPoll']);
    if (!$stmt->execute()) {
        $dbh->rollBack();
        header("Location: poll.php?$mode=$pollId&edit&err=delete");
        exit();
    }

    $stmt = $dbh->prepare(
        'INSERT INTO Question (options, result, description, idPoll)
        VALUES (:option, :result, :description, :idPoll)'
    );

    foreach ($_POST['option'] as $key =>$option) {
        if ($_POST['description'][$key] === '') {
            $dbh->rollBack();
            header("Location: poll.php?$mode=$pollId&edit&err=missingDescription");
            exit();
        }
        $jsonOption = json_encode($option);
        $jsonResult = json_encode(array_fill(0, count($option), 0));
        $stmt->bindParam(':option', $jsonOption);
        $stmt->bindParam(':result', $jsonResult);
        $stmt->bindParam(':description', $_POST['description'][$key]);
        $stmt->bindParam(':idPoll', $pollQuery['idPoll']);
        if (!$stmt->execute()) {
            $dbh->rollBack();
            header("Location: poll.php?$mode=$pollId&edit&err=insert");
            exit();
        }
    }
}


// Place file in disk
if ($image !== '') {
    $imagePath = "images/{$_SESSION['idUser']}/{$pollQuery['idPoll']}/";
    if (!is_dir($imagePath)) {
        mkdir($imagePath, 0744, true);
    }

    unlink("images/{$_SESSION['idUser']}/{$pollQuery['idPoll']}/{$pollQuery['image']}");
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath . $imageFileName)) {
        $dbh->rollBack();
        header("Location: poll.php?$mode=$pollId&edit&err=file");
        exit();
    }
}

if (mb_strtolower($_POST['visibility']) === 'private') {
    $pollId = $generatedKey;
} else {
    $pollId = $pollQuery['idPoll'];
}

$dbh->commit();

header("Location: poll.php?{$_POST['visibility']}=$pollId");
exit();
?>
