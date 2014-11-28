<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

if (!validLogin()) {
    header('Location: index.php?error=login');
    exit();
}

if (!$_POST['name'] || !$_POST['visibility'] || !$_POST['synopsis']
    || !$_POST['description'] || !$_POST['option'] || $_POST['image']
) {
    var_dump($_POST);
    die('FALHOU POST');
}


die('FIM');
?>
