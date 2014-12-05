<!DOCTYPE html>
<html>
<head>
    <title>Poll</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
<?php
if (!$loggedIn) {
    echo '
    <div id="login-area">
        <form action="processLogin.php" method="post">
            <input type="email" name="email" required="required" autofocus placeholder="Email">
            <input type="password" name="password" required="required" placeholder="Password">
            <input type="submit" value="login">
    ';
    echo "
            <input type=\"hidden\" name=\"csrf\" value=\"${_SESSION['csrf_token']}\">
        </form>
    </div>
    ";
} else {
    echo '
        <div id="login-area">
            <form action="processLogout.php" method="post">
                <input type="submit" value="logout">
    ';
    echo "
    <input type=\"hidden\" name=\"csrf\" value=\"${_SESSION['csrf_token']}\">
            </form>
        </div>
    ";
}?>
      <nav>
        <ul>
          <li><a href="index.php"><label id="home_button">Pollite</label></a></li>
          <?php if (!$loggedIn) {
            echo '<li><a href="register.php">Register</a></li>';
          } else {
            echo '<li><a href="user.php">User</a></li>
            <li><a href="pollCreate.php">Create</a></li>';
          }?>
          <li><a href="searchPoll.php">Search</a></li>
          <li><a href="about.php">About</a></li>
        </ul>
      </nav>
    </header>
