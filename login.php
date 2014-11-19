<?php
require_once 'codeIncludes/databasePipe.php';

if (!$_POST['email'] || !$_POST['password']) {
    exit('Invalid Request');
}

$stmt = $dbh->prepare(
    'SELECT email,idUser,username,hashPlusSalt,loginAttempts,lastLoginDate
    FROM UserData
    WHERE email = :email'
);
$stmt->bindParam(':email', $_POST['email']);
$stmt->execute();
$userData = $stmt->fetch();

$validCredentials = false;
//native php password_hash generates $hashAndRandomSalt, to avoid pre-computed
//attacks like: reverse dictionary attacks and Rainbow table attacks. So there is no
//need to create another variable in database called salt. The native function does
//something like $hashAndRandomSalt = hash(password+salt) . $RandomSalt
if ($userData) {
    $validCredentials
        = password_verify(
            $_POST['password'], $userData['hashPlusSalt']
        );
}

if (!$validCredentials) {
    header('Location: index.php?error=login');
    exit();
}

require_once 'codeIncludes/secureSession.php';

foreach ($userData as $key => $value) {
    if ($key === 'loginAttempts') {
        continue;
    }
    $_SESSION[$key] = $value;
}
header("Location: user.php");

exit();
?>
