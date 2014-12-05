<?php

require_once 'secureSession.php';

function sendMail($email, $url) {
  $username = 'Anonymous';
  if(isset($_SESSION['username']))
  $username = $_SESSION['username'];
  $subject = $username." has invited you to answer a poll";
  $message = "You can answer the poll by clicking the following link: ".$url;
  $headers = 'From:Pollite';
  if (mail($email,$subject,$message,$headers)){
    echo("Mail was send!");
  }else{
    echo("Failed to send email!");
  }
}

sendMail($_POST['email'], $_POST['url']);
?>
