<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

if (!validLogin()) {
    header('Location: index.php?error=login');
    exit();
}

require_once 'templates/header.php';?>
<main>
    <?php echo '<h1>login success</h1>';?>
</main>
<?php require_once 'templates/footer.php';?>
