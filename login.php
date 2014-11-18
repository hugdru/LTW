<?php
if (isset($_SESSION['lastIP'])) {
    header('Location: user.php');
    exit();
} else {
    ini_set('session.cookie_httponly', true);
}

require_once 'database/setConnection.php';

$stmt = $dbh->prepare(
    'SELECT idUser, password
    FROM UserData
    WHERE email = ?'
);
$stmt->execute(array($_POST['email']));
$userData = $stmt->fetchAll();

$validCredentials = false;
// O email é unique na base de dados no entanto
for ($i = 0; $i < count($userData); ++$i) {
    if ($userData[$i]['password'] === password_hash(
        $_POST['password'], PASSWORD_BCRYPT
    )) {
        $validCredentials = true;
        break;
    }
}

if (!$validCredentials) {
    header('Location: index.php');
    exit();
} else {
    session_start();
    $_SESSION['lastIP'] = $_SERVER['REMOTE_ADDR'];
    header("Location: user.php?id={$userData[$i]['idUser']}");
    exit;
}
?>
