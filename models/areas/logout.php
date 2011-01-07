<?    global $SiteUrl,$SiteDir;
   if(!EMPTY($_GET)){
  	 if($_GET['action']==logout){
         $user->logout();
          header('location:index.php');
         die(); 
         }
   }
?>
  <h3>welcome</h3>
   <p>Hello, <? echo $user->username ?></p>
   <a href="<? echo $_SERVER['PHP_SELF']."?action=logout" ?>">logout</a>
 
   
