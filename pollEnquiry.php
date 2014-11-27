<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

// Check if we received the correct GET
$poolEnquiryId = reset($_GET);
if ($poolEnquiryId === false) {
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
        // Check if private pollEnquiry exists
        $stmt = $dbh->prepare(
            'SELECT * FROM PollEnquiry
            WHERE generatedKey = :generatedKey'
        );
        $stmt->bindParam(':generatedKey', $poolEnquiryId);
    } else if ($mode === 'public') {
        // Check if public pollEnquiry exists and is really public
        $stmt = $dbh->prepare(
            'SELECT * FROM PollEnquiry
            WHERE
                idPollEnquiry = :idPollEnquiry AND
                idState = (SELECT idState FROM State WHERE name LIKE public)'
        );
        $stmt->bindParam(':idPollEnquiry', $poolEnquiryId);
    }
    $stmt->execute();
    $result = $stmt->fetch();
    if (!$result) {
        header('Location: index.php?err=invalidIdOrKey');
        exit();
    }

    // Check if the pollEnquiry is only:
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
                'SELECT * FROM UserPollAnswer
                WHERE
                    idPoll IN (SELECT idPoll FROM Poll WHERE idPollEnquiry = :poolEnquiryId)
                    idUser = :userId'
            );
            $stmt->bindParam(':poolEnquiryId', $result['idPollEnquiry']);
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
        if (!isset($_COOKIE['pollEnquiries'])) {
            $permission = 'submittable';
        } else {
            $parsedPollEnquiriesIds = explode(',', $_COOKIE['pollEnquiries']);
            if (array_search(
                $result['idPollEnquiry'], $parsedPollEnquiriesIds, true
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
    <div id="pollEnquiry">
        <form action="processPollEnquiryCreation.php" method="post" enctype="multipart/form-data">
            <div class="poolEnquiry-info">
                <label>Name * <input type="text" name="name" required="required"></label>
                <label>Visibility * <input type="text" name="visibility" required="required"></label>
                <label>Date * <input type="date" name="dateCreation" required="required"></label>
                <label>Synopsis <textarea name="synopsis" cols="30" rows="6" placeholder="What is this study about"></textarea></label>
            </div>
            <div class="pollEnquiry-poll">
                <h2>Poll 1</h2>
                <label>Description <textarea name="description[]" cols="30" rows="6" placeholder="Explain what this pool is for"></textarea></label>
                <label>Image <input type="file" name="image[]"></label>
                <br>
                <div>
                </div>
                <label>Option Name <input type="text" name="nameOption"></label>
                <input type="button" name="addOption" value="Add Option">
            </div>
            <input type="button" name="addPoll" value="Add Poll">
            <div class="poolEnquiry-submit">
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
<script type="text/javascript" src="javascript/pollEnquiryCreate.js" defer></script>';
}
require_once 'templates/footer.php';?>
