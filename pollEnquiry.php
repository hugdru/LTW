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

// Check validaty of ids
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
if ($mode !== 'create') {
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
</main>
<?php require_once 'templates/footer.php';?>
