<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

$loggedIn = validLogin();
require_once 'templates/header.php';?>
<main>
  <div id="home">
    <h1> Welcome to Pollite </h1>
    <div id="search">
      <h2>Newly added Polls</h2>
      <input type="hidden" id="search_bar" name="search_bar" placeholder="Poll Name">
      <select hidden="hidden" value="new" id="search_type" name="search-type">
        <option value="new">Poll Name</option>
      </select>
      <div id="search_results"> </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-1.11.1.min.js" defer></script>
  <script type="text/javascript" src="javascript/register.js" defer></script>
</main>
<?php require_once 'templates/footer.php';?>
