<?php
class Session
{
    private $_dbh;

    function __construct(&$dbh)
    {
        $this->_dbh = $dbh;

        // Custom session functions, for storage in a database.
        session_set_save_handler(
            array($this, 'open'), array($this, 'close'), array($this, 'read'),
            array($this, 'write'), array($this, 'destroy'), array($this, 'gc')
        );

        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');

        // Prevent javascript from accessing session cookie by
        // changing the php settings for this application
        ini_set('session.cookie_httponly', true);
    }

    function startSession()
    {
        // Make sure the session cookie is not accessible via javascript.
        $httponly = true;

        // Hash algorithm to use for the session.
        $session_hash = 'whirlpool';

        // Check if hash is available
        if (in_array($session_hash, hash_algos())) {
            // Set the has function.
            ini_set('session.hash_function', $session_hash);
        }

        // How many bits per character of the hash.
        ini_set('session.hash_bits_per_character', 5);

        // Force the session to only use cookies.
        ini_set('session.use_only_cookies', 1);

        // Get session cookie parameters via an array instead of ini_get each
        $cookieParams = session_get_cookie_params();
        // Set the parameters
        session_set_cookie_params(
            $cookieParams["lifetime"], $cookieParams["path"],
            $cookieParams["domain"], true, $httponly
        );

        // We start the session
        session_start();

        // Avoid session fixation attacks, to prevent malicious redirection with
        // tailored session id we generate a new one and delete the old
        if (!isset($_SESSION['valid'])) {
            session_regenerate_id(true);
            $_SESSION['valid'] = true;
        }
    }

    function open()
    {
        return true;
    }

    function close()
    {
        if ($this->_dbh->close()) {
            return true;
        }
        return false;
    }

    function read($id)
    {
        if ($this->stmt === null) {
            $this->stmt = $this->_dbh->prepare(
                "SELECT sessionData FROM UserData WHERE id = :sessionid"
            );
        }
        $this->stmt->bindParam(':sessionid', $id);
        if ($this->stmt->execute()) {
            $row = $this->stmt->fetch();
            if (!$row) {
                die('Given sessionId not found');
            }
            return $row['sessionData'];
        }
        die('Could not execute statement');
    }
}
?>
