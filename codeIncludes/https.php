<?php

// Set default_charset = "utf-8"; in php.ini if we have access

// Avoid browser automatic charset detection which may interpret
// certain strings as malicious code for XSS attacks, for instance
// if an attacker sends a byte sequence as utf-7, the html-entities
// won't detect it and hence escape them.
header('Content-Type: text/html; charset=utf-8');
ini_set('default_charset', 'utf-8'); // Some functions are aware of this value

$use_sts = true;

if ($use_sts && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    header('Strict-Transport-Security: max-age=31536000');
} else if ($use_sts) {
    header(
        'Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
        true, 301
    );
    exit();
}
?>
