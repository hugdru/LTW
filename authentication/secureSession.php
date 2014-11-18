<?php
class Session
{

    function __construct()
    {
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

    function startSession($session_name, $secure)
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

        // Force the session to only use cookies, not URL variables.
        ini_set('session.use_only_cookies', 1);

        // Get session cookie parameters
        $cookieParams = session_get_cookie_params();
        // Set the parameters
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
        // Change the session name
        session_name($session_name);
        // Now we cat start the session
        session_start();
        // This line regenerates the session and delete the old one.
        // It also generates a new encryption key in the database.
        session_regenerate_id(true);
    }
}
?>
