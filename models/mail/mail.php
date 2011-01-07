<?php
$to='djd4rce@gmail.com';
$subject='Test email';
$message="hello";
$headers="from webmaster@reversepolarity.in";
$mail_sent=@mail($to,$subject,$message,$headers);
echo $mail_sent ?"Mail Sent":"Mail Failed";
?>

