<?php
   if(!EMPTY($_POST)){
               $username = $_POST['username'];
            $password = $_POST['password'];
            $auto = $_POST['auto'];  //To remember user with a cookie for autologin
            
            $user->login($username,$password,$auto);
            
          
            if($user->signed){
           // header('location:memberarea.php');
         echo " <script language=\"javascript\" type=\"text/javascript\">window.location = \"memberarea.php\";</script>";
            die();
                

            }else{
                //Display Errors

                     foreach($user->error() as $err){
                        echo "<b>Error:</b> {$err} <br/ >";
                }
 
                }
            }
?>
   <h3>Login</h3>
   <form method="post" action="<?echo $_SERVER['PHP_SELF'] ?>">
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
        
             
