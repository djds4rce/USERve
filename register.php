<?
function get_right_sidebar(){}

 require_once("models/config.php");
function get_slug_content(){
            global $user;
            if(!EMPTY($_POST)){ 
            $registered = $user->register($_POST);
            
            if($registered){
                echo "User Registered";
            }else{
                //Display Errors
                foreach($user->error() as $err){
                        echo "<b>Error:</b> {$err} <br/ >";
                }
            }
          }

            require_once("models/areas/register-form.php");
            }
require_once("models/template.php");
?>





