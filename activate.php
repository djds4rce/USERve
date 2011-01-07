<?php
	require_once("models/config.php");
  	if(isset($_GET)){
	$state=$user->activate($_GET['confirm']);
	   if($state)
	   echo "you have sucessfully activated your account";
	   else
           echo "activation failed";
 	}
	else{
	echo "Forbidden";
	die();
	}
?>

