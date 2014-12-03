<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

if (validLogin()) {
    header('Location: user.php');
    exit();
}

if (!isset($_POST['email'], $_POST['password'])) {
    header('Location: index.php?error=missingData');
    exit();
}

if ($_POST['csrf'] !== $_SESSION['csrf_token']) {
    header('Location: index.php?error=csrf');
    exit();
}

// Strip garbage from beginning and end of string
$email = trim($_POST['email']);

// Convert email to lower case because almost
// no email provider cares about it
$email = mb_strtolower($email);

$stmt = $dbh->prepare(
    'SELECT email,idUser,username,hashPlusSalt,loginAttempts,lastLoginDate
    FROM UserData
    WHERE email = :email'
);
$stmt->bindParam(':email', $email);
$stmt->execute();
$userData = $stmt->fetch();

$validCredentials = false;
//native php password_hash generates $hashAndRandomSalt, to avoid pre-computed
//attacks like: reverse dictionary attacks and Rainbow table attacks. So there is no
//need to create another variable in database called salt. The native function does
//something like $hashAndRandomSalt = hash(password+salt) . $RandomSalt

if ($userData) {
    $validCredentials = password_verify(
        $_POST['password'], $userData['hashPlusSalt']
    );
}

if (!$validCredentials) {
    header('Location: index.php?error=login');
    exit();
}

$options = [ 'cost' => 12 ];
if (password_needs_rehash($userData['hashPlusSalt'], PASSWORD_DEFAULT, $options)) {
    $newHashPlusSalt = password_hash($_POST['password'], PASSWORD_DEFAULT, $options);
    $stmtNewHashPlusSalt = $dbh->prepare(
        'UPDATE UserData
        SET hashPlusSalt = :newHashPlusSalt
        WHERE email = :email'
    );
    $stmtNewHashPlusSalt->execute(
        array(
            ':newHashPlusSalt' => $newHashPlusSalt,
            ':email' => $email
        )
    );
}

foreach ($userData as $key => $value) {
    if ($key === 'loginAttempts') {
        continue;
    }
    $_SESSION[$key] = $value;
}

session_regenerate_id(true);

header("Location: user.php");

exit();
?>
