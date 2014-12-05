<?php

function sendMail($email, $url) {
  $subject = 'User invited to answer a poll';
  $message = 'why i so pro';
  $headers = 'From:Pollite';
  if (mail($email,$subject,$message,$headers)){
    echo("Mail was send!");
  }else{
    echo("Failed to send email!");
  }
}

sendMail($_POST['email'], $_POST['url']);
?>
