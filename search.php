<?php
require_once 'codeIncludes/databasePipe.php';

$column = $_POST['column'];
$value  = $_POST['value'];

if (isset($value, $column)) {
    try{
      if ($column  == 'email') {
        $stmt = $dbh->prepare('SELECT email FROM UserData WHERE email = :value');
      } else if ($column  == 'username') {
        $stmt = $dbh->prepare('SELECT username FROM UserData WHERE username = :value');
      } else if ($column == 'pollName') {
        $stmt = $dbh->prepare(
        'SELECT Poll.idUser, Poll.image, Poll.name as "pollName", Visibility.name as "visibility", UserData.username as "user", Poll.dateCreation as "date", Poll.idPoll
        FROM Poll, Visibility, UserData
        WHERE Poll.name LIKE :value
          AND Poll.idVisibility =Visibility.idVisibility
          AND Poll.idUser = UserData.idUser
          AND Visibility.name LIKE "Public"');
      } else if ($column == 'author') {
        $stmt = $dbh->prepare(
        'SELECT Poll.idUser, Poll.image, Poll.name as "pollName", Visibility.name as "visibility", UserData.username as "user", Poll.dateCreation as "date", Poll.idPoll
        FROM Poll, Visibility, UserData
        WHERE Poll.idUser = UserData.idUser
          AND UserData.username LIKE :value
          AND Visibility.idVisibility = Poll.idVisibility
          AND Visibility.name LIKE "Public"');
      } else if ($column == 'date') {
        $stmt = $dbh->prepare(
        'SELECT Poll.idUser, Poll.image, Poll.name as "pollName", Visibility.name as "visibility", UserData.username as "user", Poll.dateCreation as "date", Poll.idPoll
        FROM Poll, Visibility, UserData
         WHERE Poll.dateCreation LIKE :value
         AND Poll.idUser = UserData.idUser
         AND Visibility.idVisibility = Poll.idVisibility
         AND Visibility.name LIKE "Public"');
      } else if ($column == 'state') {
        $stmt = $dbh->prepare(
        'SELECT Poll.idUser, Poll.image, Poll.name as "pollName", Visibility.name as "visibility", UserData.username as "user", Poll.dateCreation as "date", Poll.idPoll
        FROM Poll, Visibility, UserData, State
        WHERE Poll.idState = State.idState
        AND State.name LIKE :value
        AND Poll.idUser = UserData.idUser
        AND Visibility.idVisibility = Poll.idVisibility
        AND Visibility.name LIKE "Public"');
      } else if ($column == 'top') {
        $stmt = $dbh->prepare('SELECT Poll.name, Poll.idPoll FROM Poll, Visibility WHERE Poll.name LIKE :value AND Visibility.name LIKE "Public" AND Visibility.idVisibility = Poll.idVisibility');
      }

        if ($column == 'pollName' || $column == 'author' || $column == 'date' || $column == 'state') {
            $valueSearch = '%'.$value.'%';
            $stmt->bindParam(':value', $valueSearch);
            $stmt->execute();
            $num = 0;
            if (!($row = $stmt->fetch())) {
                echo "";
            } else {
                echo "<ul>";
                do {
                    $num++;
                    if($row['image'])
                      echo "<span id='image'><img src=\"images/".$row['idUser']."/".$row['idPoll']."/".$row['image']."\" alt=\"\"></span>";
                    else
                      echo"<span id='image'><img /></span>";
                    echo "<li onclick=\"window.location='poll.php?Public=".$row['idPoll']."'\">";

                    echo "<p><span id='pollName'>".$row['pollName']."</span></p>
                    <p><span id='visibility'>".$row['visibility']."</span><span id='user'>".$row['user']."</span><span id='date'>".$row['date']."</span></p>
                    </li>";
                } while (($row = $stmt->fetch()) && $num < 100);
                echo "</ul>";
            }
        } else {
            $stmt->bindParam(':value', $value);
            $stmt->execute();
            if ($result = $stmt->fetch()) {
                echo $result[$column];
            } else {
                echo 'failed';
            }
        }
    } catch (PDOException $e) {
        echo 'error';
    }
}

?>
