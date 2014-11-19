<?php
require_once 'codeIncludes/databasePipe.php';

if (!$_POST['email']) {
    header('Location: index.php');
    exit();
}

$stmt = $dbh->prepare(
    'SELECT idUser, username, hashPlusSalt, lastIp, loginAttempts, lastLoginDate
    FROM UserData
    WHERE email = ?'
);
$stmt->execute(array($_POST['email']));
$stmt->store_result();
$userData = $stmt->fetch();

$validCredentials = false;

//native php password_hash generates $hashAndRandomSalt, to avoid pre-computed
//attacks like: reverse dictionary attacks and Rainbow table attacks. So there is no
//need to create another variable in database called salt. The native function does
//something like hash(password+salt) . $RandomSalt
if ($userData) {
    $validCredentials
        = password_verify(
            $_POST['password'], $userData['hashPlusSalt']
        );
}

if (!$validCredentials) {
    header('Location: index.php');
    exit();
}

require_once 'codeIncludes/secureSession.php';

foreach ($userData as $key => $value) {
    $_SESSION[$key] = $value;
}
header("Location: user.php");

exit();
?>
