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
$mode = key($_GET);
if ($mode !== 'public' && $mode !== 'private' && $mode !== 'create') {
    header('Location: index.php?err=incorrectData');
    exit();
}

// Only registered users can create enquiries
$loggedIn = validLogin();
if ((!$loggedIn) && $mode === 'create') {
    header('Location: index.php?err=login');
    exit();
}

if ($mode !== 'create') {
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
                idState = (SELECT idState FROM State WHERE name LIKE public)'
        );
        $stmt->bindParam(':idPoll', $pollId);
    }
    $stmt->execute();
    $result = $stmt->fetch();
    if (!$result) {
        header('Location: index.php?err=invalidIdOrKey');
        exit();
    }

    // Check if the poll is only:
    // editable(owner);
    // viewable(submitted once);
    // or, submittable
    // Only makes sense if user is not creating it
    if ($validLogin) {
        // For authenticated users
        if ($result['idUser'] === $_SESSION['idUser']) {
            $permission = 'editable';
        } else {
            $stmt = $dbh->prepare(
                'SELECT * FROM UserQuestionAnswer
                WHERE
                    idQuestion IN (SELECT idQueston FROM Question WHERE idPoll = :pollId)
                    idUser = :userId'
            );
            $stmt->bindParam(':pollId', $result['idPoll']);
            $stmt->bindParam(':userId', $_SESSION['idUser']);
            $stmt->execute();

            if ($stmt->fetch()) {
                $permission = 'viewable';
            } else {
                $permission = 'submittable';
            }
        }
    } else {
        // For unauthenticated users
        // Look at the cookie
        if (!isset($_COOKIE['poll'])) {
            $permission = 'submittable';
        } else {
            $parsedPollIds = explode(',', $_COOKIE['poll']);
            if (array_search(
                $result['idPoll'], $parsedPollIds, true
            )) {
                $permission = 'viewable';
            } else {
                $permission = 'submittable';
            }
        }
    }
}

require_once 'templates/header.php';?>
<main>
<?php
if ($mode === 'create') {
    // State is not here cause it will start as open
    // Not conclusion which is only activated when State is closed
    echo '
    <div id="poll">
        <form action="processPollCreation.php" method="post" enctype="multipart/form-data">
            <div class="poll-info">
                <label>Name * <input type="text" name="name" required="required"></label>
                <label>Visibility * <input type="text" name="visibility" required="required"></label>
                <label>Synopsis <textarea name="synopsis" cols="30" rows="6" placeholder="What is this study about"></textarea></label>
                <label>Image <input type="file" name="image"></label>
            </div>
            <div class="poll-question">
                <h2>Question 1</h2>
                <label>Description <textarea name="description[]" cols="30" rows="6" placeholder="Explain what this question is for"></textarea></label>
                <br>
                <div>
                </div>
                <label>Option Name <input type="text" name="nameOption"></label>
                <input type="button" name="addOption" value="Add Option">
            </div>
            <input type="button" name="addQuestion" value="Add Question">
            <div class="poll-submit">
                <input type="submit" value="send" name="Send">
            </div>
        </form>
    </div>';
}
?>
</main>
<?php
if ($mode === 'create') {
    echo '
<script src="https://code.jquery.com/jquery-1.11.1.min.js" defer></script>
<script type="text/javascript" src="javascript/pollCreate.js" defer></script>';
}
require_once 'templates/footer.php';?>