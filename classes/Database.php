<?php
class Database
{
    public $dbh;

    public function &__construct()
    {
        try {
            $this->dbh = new PDO('sqlite:sqlite/pollDatabase.db');
            $this->dbh->setAttribute(PDO::ATTR_PERSISTENT, true);
            $this->dbh->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC
            );
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        return $this->dbh;
    }
}
?>
