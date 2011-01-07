<?

//The template calls the title & site slogan

        function site_title(){
        global $SiteName;
        echo  $SiteName;
        }
        

       function site_slogan(){
       global $SiteSlogan;
       echo $SiteSlogan;
       }


//The funtions in here are called by the templates.
//The extra areas can added by creating a new function and adding a corresponding file in the /areas dir.
//The functions can be overridden in the individual pages,if we need a diffrent template for a single page.for example see login.php.

        
       if(!function_exists('get_right_sidebar')):
         function get_right_sidebar(){
         global $user;
         if(!$user->signed)
        include("areas/login-form.php");
         else
         include("areas/logout.php");
        }
      ENDIF;        
     
       
       if(!function_exists('get_left_sidebar')):
         function get_left_sidebar(){
        global $user;
        if(!$user->signed)
        include("areas/left-sidebar-public.php");
        else
        include("areas/left-sidebar-loggedin.php");
        }
      ENDIF;

       
      if(!function_exists('get_footer()')):
	function get_footer(){
       include("areas/footer.php");
        }
     ENDIF;
     if(!function_exists('get_slug_content')):
       function get_slug_content(){
       echo "<p>Extreme Georgia <em>is designed by</em> Mohd Huzairy <em>from</em> <a href=\"http://www.mentariworks.com\">MentariWorks</a></p>
			<p>And of course, <em>with a valid</em> <a href=\"#\">XHTML</a> <em>&amp;</em> <a href=\"#\">CSS</a></p>

			<p>Exclusively <em>for</em> <a href=\"http://www.smashingmagazine.com\">Smashing Magazine</a></p>";
      }
    ENDIF;
?>
