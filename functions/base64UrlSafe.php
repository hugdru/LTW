<?php
// From php.net/manual/en/function.base64-encode.php
// User Contributed Notes
function uniqueUrlBase64Encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function uniqueUrlBase64Decode($data)
{
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
?>
