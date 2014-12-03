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
      $stmt = $dbh->prepare('SELECT Poll.name, Poll.idPoll FROM Poll, Visibility WHERE Poll.name LIKE :value AND Visibility.name LIKE "Public" AND Visibility.idVisibility = Poll.idVisibility');

    if($column == 'pollName') {
      $valueSearch = '%'.$value.'%';
      $stmt->bindParam(':value', $valueSearch);
      $stmt->execute();
      $num = 0;
      if(!($row = $stmt->fetch()))
        echo "";
      else {
        echo "<ul>";
        do {
          $num++;
          echo "<li onclick=\"window.location='poll.php?Public=".$row['idPoll']."'\">".$row['name']."</li>";
        }while(($row = $stmt->fetch()) && $num < 100);
        echo "</ul>";
      }
    }
    else {
      $stmt->bindParam(':value', $value);
      $stmt->execute();
      if($result = $stmt->fetch())
        echo $result[$column];
      else
        echo 'failed';
    }
  }catch (PDOException $e) {
    echo 'error';
  }
}

?>
