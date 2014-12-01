<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

// Check if we received the correct GET
$pollId = reset($_GET);
if ($pollId === false) {
    header('Location: index.php?err=missingData');
    exit();
}
$mode = mb_strtolower(key($_GET));
if (!preg_match('/^public|private$/', $mode)) {
    header('Location: index.php?err=incorrectData');
    exit();
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
}
$stmt->execute();
$pollQuery = $stmt->fetch();
if (!$pollQuery) {
    header('Location: index.php?err=invalidId');
    exit();
}

$loggedIn = validLogin();

// Check if the poll is only:
// editable(owner);
// viewable(submitted once);
// or, answerable
// Only makes sense if user is not creating it
if ($loggedIn) {
    // For authenticated users
    if ($pollQuery['idUser'] === $_SESSION['idUser']) {
        $permission = 'editable';
    } else {
        $stmt = $dbh->prepare(
            'SELECT * FROM UserQuestionAnswer
            WHERE
                idQuestion IN (SELECT idQuestion FROM Question WHERE idPoll = :pollId) AND
                idUser = :userId'
        );
        $stmt->bindParam(':pollId', $pollQuery['idPoll']);
        $stmt->bindParam(':userId', $_SESSION['idUser']);
        $stmt->execute();

        if ($stmt->fetch()) {
            $permission = 'viewable';
        } else {
            $permission = 'answerable';
        }
    }
} else {
    // For unauthenticated users
    // Look at the cookie
    if (!isset($_COOKIE['poll'])) {
        $permission = 'answerable';
    } else {
        $parsedPollIds = explode(',', $_COOKIE['poll']);
        if (array_search(
            $pollQuery['idPoll'], $parsedPollIds, true
        ) !== false) {
            $permission = 'viewable';
        } else {
            $permission = 'answerable';
        }
    }
}

require_once 'templates/header.php';?>
<main>
<?php

$isEditMode = isset($_GET['edit']);

$stmt = $dbh->query("SELECT name FROM Visibility WHERE idVisibility = {$pollQuery['idVisibility']}");
$visibility = $stmt->fetch();
$visibility = $visibility['name'];

$stmt = $dbh->query("SELECT name FROM State WHERE idState = {$pollQuery['idState']}");
$state = $stmt->fetch();
$state = $state['name'];

$stmt = $dbh->query("SELECT idQuestion, result, options, description FROM Question WHERE idPoll = {$pollQuery['idPoll']}");
$questionsQuery = $stmt->fetchAll();

if ($permission === 'answerable') {

    include_once 'codeIncludes/pollAnswerable.php';

} else if (($permission === 'viewable') || ($permission === 'editable' && !$isEditMode)) {

    include_once 'codeIncludes/pollViewable.php';

} else if ($permission === 'editable' && $isEditMode) {

    include_once 'codeIncludes/pollEditMode.php';

}
?>
</main>
<script src="https://code.jquery.com/jquery-1.11.1.min.js" defer></script>
<?php
if ($permission === 'answerable') {
    echo '<script type="text/javascript" src="javascript/pollAnswer.js" defer></script>';
} else if ($permission === 'viewable' || !$isEditMode) {
    echo '<script type="text/javascript" src="javascript/pollView.js" defer></script>';
} else if ($permission === 'editable' && $isEditMode) {
    echo '<script type="text/javascript" src="javascript/pollCreateAndUpdate.js" defer></script>';
}
require_once 'templates/footer.php';
?>

