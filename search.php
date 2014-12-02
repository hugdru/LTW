<?php
require_once 'codeIncludes/databasePipe.php';

$column = $_POST['column'];
$value  = $_POST['value'];

if(isset($value, $column)) {
  try{
    if($column  == 'email')
      $stmt = $dbh->prepare('SELECT email FROM UserData WHERE email = :value');
    else if($column  == 'username')
      $stmt = $dbh->prepare('SELECT username FROM UserData WHERE username = :value');
    else if($column == 'pollName')
      $stmt = $dbh->prepare('SELECT name FROM Poll WHERE name = :value');
    else if($column == 'generatedKey')
      $stmt = $dbh->prepare('SELECT name FROM Poll WHERE generatedKey = :value');

    $stmt->bindParam(':value', $value);
    $stmt->execute();
    $searchResults = $stmt->fetchAll();

    if(empty($searchResults)) {
      echo 'failed';
    }
    else {
      foreach($searchResults as $result) {
        if($column == 'pollName')
          echo $result['name'];
        else
          echo $result[$column];
      }
    }

  }catch (PDOException $e) {
      echo 'error';
  }
}

?>
