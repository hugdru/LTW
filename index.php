<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

$loggedIn = validLogin();
require_once 'templates/header.php';?>
<main>
    <article>
        <h2>Hello this is a heading</h1>
        <p>This is a paragraph</p>
    </article>
    <article>
        <h2>Hello this is a heading</h1>
        <p>This is a paragraph</p>
    </article>
</main>
<?php require_once 'templates/footer.php';?>
