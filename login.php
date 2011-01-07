<?php
require_once("models/config.php");
?>
<html>
<head>
<title>Login</title>
</head>
<body>
<?
            
	    $username = $_POST['username'];
            $password = $_POST['password'];
            $auto = $_POST['auto'];  //To remember user with a cookie for autologin
            
            $user = new uFlex($username,$password,$auto);
            
            //not required, just an example usage of the built-in error reporting system
            if($user->signed){
                echo "User Successfully Logged in";
            }else{
                //Display Errors
                foreach($user->error() as $err){
                        echo "<b>Error:</b> {$err} <br/ >";
                }
            }
?>
        <form method="post" action="login.php">
            <label>Username:</label>
            <input name="username" type="text">
            <br>
            
            <label>Password:</label>
            <input name="password" type="password">
            <br>
            
            <label>Remember me?:</label>
            <input name="auto" type="checkbox">
            <br>
            
            <input value="login" type="submit">
        </form>
</body>
</html>
