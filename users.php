<?php
require_once 'functions/validLogin.php';

if (!validLogin()) {
    header('Location: index.php?error=login');
    exit();
}
echo 'login success'
?>
