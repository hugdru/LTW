<?php

function sendMail($email, $url) {
  $subject = 'User invited to answer a poll';
  $message = 'why i so pro';
  $headers = 'From:Pollite';
  if (mail($email,$subject,$message,$headers)){
    echo("succeded");
  }else{
    echo("failed");
  }
}

sendMail($_POST['email'], $_POST['url']);
?>
