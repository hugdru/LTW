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
            <input type="email" id="emailReg" class="register-input" name="email" required="required" autofocus placeholder="Email">
            <span  id="errormsg_email" class="errormsg">email already exists</span>
            <input type="text" id="usernameReg" class="register-input" name="username" required="required" placeholder="Username">
            <span  id="errormsg_username" class="errormsg"></span>
            <input type="password" id="passwordReg" class="register-input" name="password" required="required" placeholder="Password">
            <span  id="errormsg_password" class="errormsg"></span>
            <input type="password" id="passwordAgainReg" class="register-input" name="passwordAgain" required="required" placeholder="Repeat Password">
            <span  id="errormsg_passwordAgain" class="errormsg"></span>
            <textarea class="register-input" name="about" cols="30" rows="3" placeholder="My hobbies, some personal stuff I want to share with the community"></textarea>
            <span id="recaptcha">
              <?php require_once 'codeIncludes/recaptchaClientSide.php'?>
            </span>
            <input type="submit" class="register-input" id="register" value="Register">
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-1.11.1.min.js" defer></script>
    <script type="text/javascript" src="javascript/register.js" defer></script>
</main>
<?php require_once 'templates/footer.php';
