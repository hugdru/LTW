<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

if (!validLogin()) {
    header('Location: index.php?error=login');
    exit();
}
echo '<h1>login success</h1>'
?>
