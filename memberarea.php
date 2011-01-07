<? require_once("models/config.php");
function get_slug_content(){
            global $user;
            if($user->signed)
            {
            echo "<p>You are signed in as ".$user->username."</p>";
            echo "Your User id is".$user->id."</p>";
            }
            else
            require_once("models/areas/login-form.php");  
            }
require_once("models/template.php");
?>





