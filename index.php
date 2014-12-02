<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

$loggedIn = validLogin();
require_once 'templates/header.php';?>
<main>
    <h1>Search by name</h1>
    <div id="search">
      <input type="text" id="search_bar" name="search_bar" placeholder="Search">
      <span id="search_results"> </span>
    </div>
    <script src="https://code.jquery.com/jquery-1.11.1.min.js" defer></script>
    <script type="text/javascript" src="javascript/register.js" defer></script>
</main>
<?php require_once 'templates/footer.php';?>
