<?php
	function user_reg_mail($sub,$hash,$mailid){        
        global $SiteUrl,$SiteDir;
        $to=$mailid;
        $subject=$sub;
        $message="your are recieving this mail because you have registered yourself on the site ".$SiteUrl." To confirm your registeration click on the link below http://".$SiteUrl.$SiteDir."activate.php?confirm=".$hash." if you did not register at the site kindly ignore the message";
        
        $headers="from no-reply@i.com";
        @mail($to,$subject,$message,$headers);
}
?>
