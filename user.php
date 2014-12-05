<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

if (!($loggedIn = validLogin())) {
    header('Location: index.php?error=login');
    exit();
}

require_once 'templates/header.php';?>
<main>
    <a href="pollCreate.php">Create Poll</a>
    <div id="search">
      <h2>Search My Polls</h2>
      <input type="text" id="search_bar" name="search_bar" placeholder="Poll Name">
      <select id="search_type" name="search-type">
        <option value="public">Public</option>
        <option value="private">Private</option>
        <option value="open">Open</option>
        <option value="closed">Closed</option>
      </select>
      <div id="search_results"> </div>
    </div>
    <script src="https://code.jquery.com/jquery-1.11.1.min.js" defer></script>
    <script type="text/javascript" src="javascript/register.js" defer></script>
</main>
<?php require_once 'templates/footer.php';?>
