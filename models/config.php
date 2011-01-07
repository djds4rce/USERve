<?php
/* YACMS Configuration file*/
	require_once("settings.php");
	require_once("class.uFlex.php");
        require_once("sendmail.php");
	require_once("funcs.template.php");
        $user= new uFlex();
        
	if(is_dir("install/"))
       		{
        	 header("location:install/");
       		}
//database connection
	$con=mysql_connect($databasehost,$databaseusername,$databasepassword);
		if(!$con)
		{
		die("coudnt not connect to the database");
		}

	$db=mysql_select_db($databasename);
	 	if(!$db)
		{
		 die("coudnt not select database");
		}
	
?>					
