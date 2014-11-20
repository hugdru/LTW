<?php
// Cookies should only be sent from client to server via a secure connection, https.
$secure = true;

// Avoids session fixation attacks by preventing passing session ids in urls
if (ini_set('session.use_only_cookies', 1) === false) {
    header("Location: ../error.php?err=Could not initiate a safe session");
    exit();
}

// Make sure the session cookie is only accessible through the HTTP protocol,
// which means cookie won't be accessible by scripting languages.
// Reduces indentity theft via XSS attacks.
$httpOnly = true;

// Hash algorithm to use for the session.
$sessionHash = 'whirlpool';

// Check if hash is available
if (in_array($sessionHash, hash_algos())) {
    // Set the has function.
    ini_set('session.hash_function', $sessionHash);
}

// How many bits per character of the hash.
ini_set('session.hash_bits_per_character', 5);

// Get session cookie parameters via an array instead of ini_get each
$cookieParams = session_get_cookie_params();
// Set the parameters
session_set_cookie_params(
    $cookieParams["lifetime"], $cookieParams["path"],
    $cookieParams["domain"], $httpOnly, $secure
);

// We start the session
session_start();

// Avoid session fixation attacks, to prevent malicious redirection with
// tailored session id we generate a new one and delete the old
if ($_SESSION['initiated'] === null) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}
?>
