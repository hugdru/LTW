<?php
try {
    $dbh = new PDO('sqlite:sqlite/pollDatabase.db');
    $dbh->setAttribute(PDO::ATTR_PERSISTENT, true);
    $dbh->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC
    );
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die($e->getMessage());
}
?>
