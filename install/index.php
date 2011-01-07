<?php
require_once("../models/config.php");
?>
<html>
<head>
<title>Userve database setup</title>
</head>
<body>
<?
require_once("../models/config.php");
             	if(!(mysql_query("SELECT * FROM users;")>0))
        {

             if(isset($_GET["install"]))
        

                {    		$sql="CREATE TABLE IF NOT EXISTS `users` (
		        `user_id` int(7) NOT NULL AUTO_INCREMENT,
  		        `username` varchar(15) NOT NULL,
  		 	`password` varchar(35) NOT NULL,
                        `email` varchar(35) NOT NULL,
      		        `activated` int(1) NOT NULL DEFAULT '0',
 		        `confirmation` varchar(35) NOT NULL,
		        `reg_date` int(11) NOT NULL,
		        `last_login` int(11) NOT NULL DEFAULT '0',
		        `group_id` int(2) NOT NULL DEFAULT '1',
		         PRIMARY KEY (`user_id`)
                         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
                     mysql_query($sql);
                     echo"<p>install sucessfull,please delete the install folder to continue</p>";
                    }
              else {

                  echo"<a href=\"?install=true\">Install YACMS</a>";




                   }
        }
        else
        {
           echo"<p>Already installed please delete the install folder to continue</p>";
        }
?>
</body>
</html>
