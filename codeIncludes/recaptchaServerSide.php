<?php
require_once 'externalScripts/recaptcha-1.11/recaptchalib.php';

$privatekey = "6LdxQP4SAAAAAGQQtcKdozWSJoD2YRE8EqzpWFAl";
$resp = recaptcha_check_answer(
    $privatekey,
    $_SERVER["REMOTE_ADDR"],
    $_POST["recaptcha_challenge_field"],
    $_POST["recaptcha_response_field"]
);
?>
