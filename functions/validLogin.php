<?php
function validLogin()
{
    if ($_SESSION['initiated'] !== null && $_SESSION['email'] !== null
        && $_SESSION['idUser'] !== null && $_SESSION['username'] !== null
        && $_SESSION['hashPlusSalt'] !== null && $_SESSION['lastLoginDate'] !== null
    ) {
        return true;
    } else {
        return false;
    }
}
?>
