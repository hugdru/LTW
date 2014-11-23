<?php
function validLogin()
{
    if (isset(
        $_SESSION['email'], $_SESSION['idUser'],
        $_SESSION['username'], $_SESSION['hashPlusSalt'], $_SESSION['lastLoginDate'])
    ) {
        return true;
    } else {
        return false;
    }
}
?>
