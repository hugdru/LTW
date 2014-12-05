<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

$loggedIn = validLogin();
require_once 'templates/header.php';?>
<main>
  <div id="about">
    <img src="resources/images/feup-logo.gif" alt="FEUP logo"/>
    <h2>Trabalho da disciplina de LTW</h2>
    <p>Realizado por:</p>
    <p><label>Filipe Marques </label><label>nr: 201302811</label></p>
    <p><label>Hugo Drumond    </label><label>nr: 201102900</label></p>
    <p><label>José Amorim     </label><label>nr: 201206111</label></p>
  </div>
</main>
<?php require_once 'templates/footer.php';?>
