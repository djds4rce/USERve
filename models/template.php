<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title><?php site_title?></title>
	<link rel="stylesheet" href="models/css/stylesheet.css" type="text/css" media="screen" /> 
</head>

<body>

	<div id="wrapper"><!-- Begin wrapper -->
	
		<div id="header"><!-- Begin header -->
			<h1><a href="<? $SiteUrl."/".SiteDir ?>" title="Back to home"><? site_title();?></a></h1>
			<p><? site_slogan(); ?></p>
		</div>
		
		<hr />
		
		<div id="outer-space"><!-- Begin outer-space -->
		
			<div id="hfeed">
				<div class="hentry">				
					<div class="entry-content">
				        <? get_slug_content(); ?>
                  			</div>
					
			</div>
		
					
			</div>
			
			<hr />
			
			<div id="left-sidebar">
			
				<div class="widget">
			        <? get_left_sidebar(); ?>
        			</div>
			</div>
			
			<div class="clear"></div>
			
		</div><!-- End outer-space -->
		
		<hr />
		
		<div id="right-sidebar">
		
			<div class="widget">
	                <?php get_right_sidebar(); ?> 
			</div>
			
		
		</div>
		
		<div class="clear"></div>
		
		<hr />
		
	 	<div id="footer">
		<? get_footer(); ?>
		</div>
	
	</div><!-- End wrapper -->

</body>
</html>
