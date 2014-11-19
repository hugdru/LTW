<?php
$use_sts = true;

if ($use_sts && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    header('Strict-Transport-Security: max-age=31536000');
} elseif ($use_sts) {
    header(
        'Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
        true, 301
    );
    exit();
}

// Cookies should only be sent from client to server via a secure connection, https.
$secure = true;

// Avoids session fixation attacks by preventing passing session ids in urls
if (ini_set('session.use_only_cookies', 1) === false) {
    header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
    exit();
}

// Make sure the session cookie is only accessible through the HTTP protocol,
// which means cookie won't be accessible by scripting languages.
// Reduces indentity theft via XSS attacks.
$httpOnly = true;

// Hash algorithm to use for the session.
$sessionHash = 'whirlpool';

// Check if hash is available
if (in_array($session_hash, hash_algos())) {
    // Set the has function.
    ini_set('session.hash_function', $session_hash);
}

// How many bits per character of the hash.
ini_set('session.hash_bits_per_character', 5);

// Get session cookie parameters via an array instead of ini_get each
$cookieParams = session_get_cookie_params();
// Set the parameters
session_set_cookie_params(
    $cookieParams["lifetime"], $cookieParams["path"],
    $cookieParams["domain"], $https, $secure
);

// We start the session
session_start();

// Avoid session fixation attacks, to prevent malicious redirection with
// tailored session id we generate a new one and delete the old
session_regenerate_id(true);
?>
