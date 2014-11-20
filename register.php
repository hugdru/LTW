<?php
require_once 'codeIncludes/https.php';
require_once 'codeIncludes/databasePipe.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Poll Enquiries</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div id="login-area">
            <form action="processLogin.php" method="post">
                <input type="email" name="email" required="required" autofocus placeholder="Email">
                <input type="password" name="password" required="required" placeholder="Password">
                <input type="submit" value="send">
            </form>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li><li><a href="register.php">Register</a></li><li><a href="about.php">About</a></li>
            </ul>
        </nav>
    </header>
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
    <footer>
    </footer>
</body>
</html>
