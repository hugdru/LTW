<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

$loggedIn = validLogin();
require_once 'templates/header.php';?>
<main>
  <img src="resources/images/feup-logo.gif" alt="FEUP logo"/>
  <h1>Trabalho da disciplina de LTW</h1>
  <p>Realizado por:</p>
  <p>Filipe Marques número: 201302811</p>
  <p>Hugo Drumond   número: 201102900</p>
  <p>José Amorim número: 201206111</p>
</main>
<?php require_once 'templates/footer.php';?>
