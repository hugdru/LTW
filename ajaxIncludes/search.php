<?php
require_once '../codeIncludes/databasePipe.php';

$column = $_POST['column'];
$value  = $_POST['value'];

if(isset($value, $column)) {
  try{
    if($column  == 'email')
      $stmt = $dbh->prepare('SELECT email FROM UserData WHERE email = :value');
    else if($column  == 'username')
      $stmt = $dbh->prepare('SELECT username FROM UserData WHERE username = :value');
      
    $stmt->bindParam(':value', $value);
    $stmt->execute();
    $searchResults = $stmt->fetchAll();

    if(empty($searchResults)) {
      echo 'failed';
    }
    else {
      echo 'success';
    }

  }catch (PDOException $e) {
      echo $e->getMessage();
  }
}

?>
