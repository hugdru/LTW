<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

if (!isset($_POST['option'], $_POST['csrf'], $_POST['pollId'], $_POST['mode'])) {
    header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=invalidData");
    exit();
}

if ($_POST['csrf'] !== $_SESSION['csrf_token']) {
    header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=csrf");
    exit();
}

$mode = mb_strtolower($_POST['mode']);
if (!preg_match('/^public|private$/', $mode)) {
    header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=mode");
    exit();
}

if ($mode === 'private') {
    // Check if private poll exists
    $stmt = $dbh->prepare(
        'SELECT * FROM Poll
        WHERE generatedKey = :generatedKey'
    );
    $stmt->bindParam(':generatedKey', $_POST['pollId']);
} else if ($mode === 'public') {
    // Check if public poll exists and is really public
    $stmt = $dbh->prepare(
        'SELECT * FROM Poll
        WHERE
            idPoll = :idPoll AND
            idVisibility = (SELECT idVisibility FROM Visibility WHERE name LIKE \'public\')'
    );
    $stmt->bindParam(':idPoll', $_POST['pollId']);
}
$stmt->execute();
$result = $stmt->fetch();
if (!$result) {
    header('Location: index.php?err=invalidId');
    exit();
}

$stmt = $dbh->prepare(
    'SELECT idQuestion FROM Question WHERE idPoll = :pollId'
);
$stmt->bindParam(':pollId', $result['idPoll']);
$stmt->execute();
if (!($questions = $stmt->fetchAll())) {
    header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=noQuestions");
    exit();
}

$stringQuestions = '';
$arrayLength = count($questions);

if ($arrayLength != count($_POST['option'])) {
    header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=missingRadio");
    exit();
}

for ($i = 0; $i < $arrayLength; ++$i) {
    if ($i !== ($arrayLength - 1)) {
        $stringQuestions .= $questions[$i]['idQuestion'] . ',';
    } else {
        $stringQuestions .= $questions[$i]['idQuestion'];
    }
}

$loggedIn = validLogin();

// It is necessary to do this again cause a malicious user may try to answer twice
if ($loggedIn) {
    $stmt = $dbh->prepare(
        'SELECT * FROM UserQuestionAnswer
        WHERE
            idQuestion IN (:questions) AND
            idUser = :userId'
    );
    $stmt->bindParam(':userId', $_SESSION['idUser']);
    $stmt->bindParam(':questions', $stringQuestions);
    $stmt->execute();
    if ($stmt->fetch()) {
        header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=duplicate");
        exit();
    }
} else {
    if (($cookieSet = isset($_cookie['poll']))) {
        $parsedPollIds = explode(',', $_COOKIE['poll']);
        if (array_search(
            $_POST['pollId'], $parsedPollIds, true
        ) !== false) {
            header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=duplicate");
            exit();
        }
    }
}

$arrayQuestions = explode(',', $stringQuestions);

$stmtVerifyRadio = $dbh->prepare(
    'SELECT options, result FROM Question
    WHERE idQuestion = :idQuestion'
);

$stmtUpdateQuestion = $dbh->prepare(
    'UPDATE Question
    SET result = :result
    WHERE idQuestion = :idQuestion'
);

$dbh->beginTransaction();
// Do the necessary steps so user cant answer twice to the same poll
if ($loggedIn) {
    $stmt = $dbh->prepare(
        'INSERT INTO UserQuestionAnswer
            (idQuestion, idUser, dateDone, optionSelected)
        VALUES
            (:idQuestion, :idUser, :dateDone, :optionSelected)'
    );
    $stmt->bindParam(':idUser', $_SESSION['idUser']);
    $stmt->bindValue(':dateDone', date('Y-m-d'));

    foreach ($arrayQuestions as $key => $question) {
        $stmtVerifyRadio->bindParam(':idQuestion', $question);
        $stmtVerifyRadio->execute();
        if (!($questionFetch = $stmtVerifyRadio->fetch())) {
            header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=missingDbData");
            $dbh->rollBack();
            exit();
        }
        $options = json_decode($questionFetch['options']);
        if (($indexNeedle = array_search($_POST['option'][$key], $options, true)) === false) {
            header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=noSuchOption");
            $dbh->rollBack();
            exit();
        }

        $stmt->bindParam(':idQuestion', $question);
        $stmt->bindValue(':optionSelected', json_encode($_POST['option'][$key]));
        try {
            if (!$stmt->execute()) {
                header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=failedInsert");
                $dbh->rollBack();
                exit();
            }
        } catch (PDOException $e) {
            switch ($e->errorInfo[0]) {
            case '23000':
                header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=duplicate");
                $dbh->rollBack();
                exit();
            default:
                $dbh->rollBack();
                die('Unexpected database error');
            }
        }
        // Update the Question result so we don't have to count them via selects
        // Doing that on each page reload would be too expensive
        $updatedResult = json_decode($questionFetch['result']);
        ++$updatedResult[$indexNeedle];
        $updatedResult = json_encode($updatedResult);

        $stmtUpdateQuestion->bindParam(':result', $updatedResult);
        $stmtUpdateQuestion->bindParam(':idQuestion', $question);

        if (!$stmtUpdateQuestion->execute()) {
            header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=failedUpdate");
            $dbh->rollBack();
            exit();
        }
    }
} else {

    if (!$cookieSet) {
        setcookie('poll', $result['idPoll'], time() + (86400 * 180), '/');
    } else {
        $_COOKIE['poll'] .= ",{$result['idPoll']}";
    }

    $stmt = $dbh->prepare(
        'INSERT INTO UnauthenticatedQuestionAnswer
            (idQuestion, dateDone, optionSelected)
        VALUES
            (:idQuestion, :dateDone, :optionSelected)'
    );
    $stmt->bindValue(':dateDone', date('Y-m-d'));

    foreach ($arrayQuestions as $key => $question) {
        $stmtVerifyRadio->bindParam(':idQuestion', $question);
        $stmtVerifyRadio->execute();
        if (!($questionFetch = $stmtVerifyRadio->fetch())) {
            header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=missingDbData");
            $dbh->rollBack();
            exit();
        }
        $options = json_decode($questionFetch['options']);
        if (($indexNeedle = array_search($_POST['option'][$key], $options, true)) === false) {
            header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=noSuchOption");
            $dbh->rollBack();
            exit();
        }

        $stmt->bindParam(':idQuestion', $question);
        $stmt->bindValue(':optionSelected', json_encode($_POST['option'][$key]));
        try {
            if (!$stmt->execute()) {
                header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=failedInsert");
                $dbh->rollBack();
                exit();
            }
        } catch (PDOException $e) {
            switch ($e->errorInfo[0]) {
            case '23000':
                header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=duplicate");
                $dbh->rollBack();
                exit();
            default:
                $dbh->rollBack();
                die('Unexpected database error');
            }
        }

        // Update the Question result so we don't have to count them via selects
        // Doing that on each page reload would be too expensive
        $updatedResult = json_decode($questionFetch['result']);
        ++$updatedResult[$indexNeedle];
        $updatedResult = json_encode($updatedResult);

        $stmtUpdateQuestion->bindParam(':result', $updatedResult);
        $stmtUpdateQuestion->bindParam(':idQuestion', $question);

        if (!$stmtUpdateQuestion->execute()) {
            header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}&err=failedUpdate");
            $dbh->rollBack();
            exit();
        }
    }
}
$dbh->commit();

header("Location: poll.php?{$_POST['mode']}={$_POST['pollId']}");
exit();

?>
