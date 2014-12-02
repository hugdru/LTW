<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/secureSession.php';
require_once 'functions/validLogin.php';

if ($loggedIn = validLogin()) {
    header('Location: index.php');
}

require_once 'templates/header.php';?>
<main>
    <div id="register-area">
        <h1>Register</h1>
        <form action="processRegistration.php" method="post">
            <input type="email" name="email" required="required" autofocus placeholder="Email">
            <input type="text" name="username" required="required" placeholder="Username">
            <input type="password" name="password" required="required" placeholder="Password">
            <input type="password" name="passwordAgain" required="required" placeholder="Repeat Password">
            <textarea name="about" cols="30" rows="4" placeholder="My hobbies, some personal stuff I want to share with the community"></textarea>
            <input type="submit" id="register" value="Register">
        </form>
    </div>
</main>
<?php require_once 'templates/footer.php';
