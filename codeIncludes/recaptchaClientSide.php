<?php
require_once 'externalScripts/recaptcha-1.11/recaptchalib.php';
$publickey = "6LdxQP4SAAAAANYYEoaT6FTUfxqcTNrUHP1Qxo3x";
echo recaptcha_get_html($publickey, null, true);
?>
