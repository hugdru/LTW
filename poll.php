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
if (!preg_match('/^public|private|create$/', $mode)) {
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
                idVisibility = (SELECT idVisibility FROM Visibility WHERE name LIKE \'public\')'
        );
        $stmt->bindParam(':idPoll', $pollId);
    }
    $stmt->execute();
    $result = $stmt->fetch();
    if (!$result) {
        header('Location: index.php?err=invalidId');
        exit();
    }

    // Check if the poll is only:
    // editable(owner);
    // viewable(submitted once);
    // or, submittable
    // Only makes sense if user is not creating it
    if ($loggedIn) {
        // For authenticated users
        if ($result['idUser'] === $_SESSION['idUser']) {
            $permission = 'editable';
        } else {
            $stmt = $dbh->prepare(
                'SELECT * FROM UserQuestionAnswer
                WHERE
                    idQuestion IN (SELECT idQuestion FROM Question WHERE idPoll = :pollId) AND
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
            ) !== false) {
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

    $stmt = $dbh->query('SELECT name FROM Visibility');
    $visibility = $stmt->fetchAll();

    // State is not here cause it will start as open
    // Not conclusion which is only activated when State is closed
    echo '
    <div id="poll">
        <form action="processPollCreation.php" method="post" enctype="multipart/form-data" onsubmit="return verifyQuestions();">
            <div class="poll-info">
                <label>Name * <input type="text" name="name" required="required"></label>
                <fieldset style="display: inline"><legend>Visibility *</legend>';
    $i = 0;
    foreach ($visibility as $value) {
        if ($i === 0) {
            echo    "<label>{$value['name']} <input type=\"radio\" name=\"visibility\" value=\"{$value['name']}\" checked></label><br>";
        } else {
            echo    "<label>{$value['name']} <input type=\"radio\" name=\"visibility\" value=\"{$value['name']}\"></label><br>";
        }
        ++$i;
    }
    echo        '</fieldset>
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
            <div class="poll-submit">';
        echo "<input type=\"hidden\" name=\"csrf\" value=\"${_SESSION['csrf_token']}\">";
        echo '<input type="submit" value="send" name="Send">
            </div>
        </form>
    </div>';
} else {

    $stmt = $dbh->query("SELECT name FROM Visibility WHERE idVisibility = {$result['idVisibility']}");
    $visibility = $stmt->fetch();
    $visibility = $visibility['name'];

    $stmt = $dbh->query("SELECT name FROM State WHERE idState = {$result['idState']}");
    $state = $stmt->fetch();
    $state = $state['name'];

    $stmt = $dbh->query("SELECT idQuestion, options, description FROM Question WHERE idPoll = {$result['idPoll']}");
    $options = $stmt->fetchAll();

    echo '<div id="poll">';
    if ($permission === 'submittable') {
        echo '<div class="poll-info">';
            echo "<h2>{$result['name']}</h2>
                <p><span class=\"fields\">Visibility: </span>$visibility</p>
                <p><span class=\"fields\">State: </span>$state</p>";
        if ($result['synopsis']) {
            $encodedSynopsis = htmlentities($result['synopsis']);
            echo "<h3>Synopsis</h3>
                <p>$encodedSynopsis</p>";
        }
        if ($result['image']) {
            echo "<img src=\"images/{$result['idUser']}/{$result['idPoll']}/{$result['image']}\" alt=\"\">";
        }
        echo '</div>
            <form action="processPollSubmit.php" method="post" onsubmit="return verifyRadios();">';
        foreach ($options as $key => $option) {
            echo '<div class="poll-question">';
            echo "<h3>Question " . ($key + 1) . "</h3>";
            if ($option['description']) {
                $encodedDescription = htmlentities($option['description']);
                echo "<p>$encodedDescription</p>";
            }
            $decodedRadios = json_decode($option['options'], true);
            echo '<div>';
            foreach ($decodedRadios as $decodedRadio) {
                echo "<label>$decodedRadio <input type=\"radio\" name=\"option[$key]\" value=\"$decodedRadio\"></label><br>";
            }
            echo '</div></div>';
        }
        echo '<div class="poll-submit">';

        echo "<input type=\"hidden\" name=\"csrf\" value=\"${_SESSION['csrf_token']}\">";
        echo "<input type=\"hidden\" name=\"pollId\" value=\"$pollId\">";
        echo "<input type=\"hidden\" name=\"mode\" value=\"$mode\">";
        echo '<input type="submit" value="send" name="Send">
        </div></form></div>';
    } else if ($permission === 'viewable') {

    } else if ($permission === 'editable') {

    }
}
?>
</main>
<script src="https://code.jquery.com/jquery-1.11.1.min.js" defer></script>
<?php
if ($mode === 'create') {
    echo '<script type="text/javascript" src="javascript/pollCreate.js" defer></script>';
} else if ($permission === 'submittable') {
    echo'<script type="text/javascript" src="javascript/pollSubmit.js" defer></script>';
}

require_once 'templates/footer.php';?>
