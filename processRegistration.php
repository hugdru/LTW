<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';
require_once 'functions/validLogin.php';

if (validLogin()) {
    header('Location: user.php');
    exit();
}

if (!$_POST['email'] || !$_POST['password'] || !$_POST['username']) {
    header('Location: register.php?error=missingData');
    exit();
}

$passwordLength = strlen($_POST['password']);

if ($passwordLength > 72) {
    header('Location: register.php?error=passwordLong');
    exit();
}
if ($passwordLength < 8) {
    header('Location: register.php?error=passwordShort');
    exit();
}

if (strlen($_POST['username']) < 4) {
    header('Location: register.php?error=usernameShort');
    exit();
}

// Check if email and username exists in database
//$stmt = $dbh->prepare(
    //'SELECT count(*) FROM
    //(SELECT * FROM UserData WHERE email = :email
    //UNION
    //SELECT * FROM userData WHERE username = :username)'
//);

//$stmt->bindParam(':email', $_POST['email']);
//$stmt->bindParam(':username', $_POST['username']);
//$emailOrUsernameExists = $stmt->fetch();

//if ($emailOrUsernameExists) {
    //header('Location: register.php?error=emailOrUsername');
    //exit();
//}

// Check if email exists in database
$stmt = $dbh->prepare(
    'SELECT email FROM UserData WHERE email = :email'
);
$stmt->bindParam(':email', $_POST['email']);
$exists = $stmt->fetch();
if ($exists) {
    header('Location: register.php?error=emailExists');
    exit();
}

// Check if username exists in database
$stmt = $dbh->prepare(
    'SELECT username FROM UserData WHERE username = :username'
);
$stmt->bindParam(':username', $_POST['username']);
$exists = $stmt->fetch();
if ($exists) {
    header('Location: register.php?error=usernameExists');
    exit();
}

$hashPlusSalt = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $dbh->prepare(
    'INSERT INTO UserData
    (email, hashPlusSalt, loginAttempts, lastLoginDate, username, lastIp, about)
    VALUES (:email, :hashPlusSalt, 0, \'0\', :username, \'0\', :about)'
);
if (!$stmt->execute(
    array(
        ':email' => $_POST['email'],
        ':hashPlusSalt' => $hashPlusSalt,
        ':username' => $_POST['username'],
        ':about' => $_POST['about']
    )
)) {
    header('Location: register.php?error=failedInsert');
    exit(0);
}

header('Location: index.php');
exit(0);
?>
