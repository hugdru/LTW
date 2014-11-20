<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';

require_once 'templates/header.php';?>
<main>
    <div id="register-area">
        <form action="processRegistration.php" method="post">
            <label>Email:<input type="email" name="email" required="required" autofocus placeholder="john@corporation.me"></label>
            <label>Password<input type="password" name="password" required="required" placeholder="myComplexPassword"></label>
            <label>Username:<input type="text" name="username" required="required" placeholder="theHawk"></label>
            <label>About:<textarea name="about" cols="30" rows="6" placeholder="My hobbies, some personal stuff I want to share with the community"></textarea></label>
            <input type="submit" value="send">
        </form>
    </div>
</main>
<?php require_once 'templates/footer.php';
