<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

if (validLogin()) {
    header('Location: user.php');
    exit();
}

if (!$_POST['email'] || !$_POST['password'] || !$_POST['username']) {
    header('Location: register.php?error=missingData');
    exit();
}

require_once 'codeIncludes/recaptchaServerSide.php';
if (!$resp->is_valid) {
    header('Location: register.php?error=botControl');
    exit();
}

// Strip garbage from beginning and end of string
$email = trim($_POST['email']);
$username = trim($_POST['username']);

// Convert email to lower case because almost
// no email provider cares about it
$email = mb_strtolower($email);
$username = mb_strtolower($username);

// Check if email is valid
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?error=emailInvalid');
    exit();
}

$error = null;
// Check if password is valid
$password = $_POST['password'];
$passwordLength = strlen($password);

if ($passwordLength > 72) {
    $error = 'passwordLong';
} else if ($passwordLength < 8) {
    $error = 'passwordShort';
} else {
    if (!preg_match('/[0-9]+/', $password)) {
        $error .= 'passwordNumber';
    }
    if (!preg_match('/[a-z]+/', $password)) {
        $error .= 'passwordUncap';
    }
    if (!preg_match('/[A-Z]+/', $password)) {
        $error .= 'passwordCap';
    }
    if (!preg_match('/\W+/', $password)) {
        $error .= "passwordSymbol";
    }
}
if ($error !== null) {
    header("Location: register.php?error=$error");
    exit();
}

// Check if username is valid
if (!preg_match(
    '/^[a-z][a-z0-9\.\-_]{3,19}$/', $username
)) {
    $error = 'usernameInvalid';
}
if ($error !== null) {
    header("Location: register.php?error=$error");
    exit();
}

// Check if about is valid
$about = $_POST['about'];


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
$stmt->bindParam(':email', $email);
$stmt->execute();
$exists = $stmt->fetch();
if ($exists) {
    header('Location: register.php?error=emailExists');
    exit();
}

// Check if username exists in database
$stmt = $dbh->prepare(
    'SELECT username FROM UserData WHERE username = :username'
);
$stmt->bindParam(':username', $username);
$stmt->execute();
$exists = $stmt->fetch();
if ($exists) {
    header('Location: register.php?error=usernameExists');
    exit();
}

$options = ['cost' => 12];
$hashPlusSalt = password_hash($_POST['password'], PASSWORD_DEFAULT, $options);

$stmt = $dbh->prepare(
    'INSERT INTO UserData
    (email, hashPlusSalt, loginAttempts, lastLoginDate, username, lastIp, about)
    VALUES (:email, :hashPlusSalt, 0, \'0\', :username, \'0\', :about)'
);
if (!$stmt->execute(
    array(
        ':email' => $email,
        ':hashPlusSalt' => $hashPlusSalt,
        ':username' => $username,
        ':about' => $about
    )
)) {
    header('Location: register.php?error=failedInsert');
    exit(0);
}

header('Location: index.php');
exit(0);
?>
