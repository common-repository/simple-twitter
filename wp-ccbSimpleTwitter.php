<?php
/*
Plugin Name: Simple Twitter
Plugin URI: http://www.chriscb.org/wpPlugins-simpleTwitter
Description: Print your last twitts in a fancy fading window... 
Version: 1.0
Author: Chris "Chris CB" CUSSAT-BLANC
Author URI: http://www.chriscb.org/
Author eMail: chriscb@chriscb.com
Text Domain: Simple Twitter
*/
?>
<?php
/*  Copyright 2009  Christophe CUSSAT-BLANC  (email : chriscb@chriscb.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php

register_activation_hook(__FILE__, 'chriscb_simpleTwitterActivation');

// Add the admin settings page
add_action('admin_menu', 'chriscb_simpleTwitterMenuActivation');

// Add the link to the settings page in the plugins list.
add_filter('plugin_action_links', 'ccb_simpleTwitter_addSettingsLink', 10, 2 );

// Really add the settings page.
function chriscb_simpleTwitterMenuActivation() {
	add_plugins_page( 'Simple Twitter', 'Simple Twitter', 'manage_options', 'ccbSimpleTwitterAdminMenu', 'chriscb_simpleTwitterAdminMenu');
}	


/* ******************************

		SETTINGS PAGE
		
   ******************************/

function chriscb_simpleTwitterAdminMenu() {
	// check that the user has the required capability 
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
	$hiddenName = "simpleTwitter_submit";
	
	// Option Name, and explanation, and type, and...
	$n=0;
	
	$optName[$n]='username';
	$optCapt[$n]='Twitter User ID';
	$optUnit[$n]='';
	$optType[$n]='text';
	$optExpl[$n]='Your Twitter user-ID.';
	$optParams[$n]['required']=true;
	$optParams[$n]['size']=40;
	
	$n++;
	$optName[]='replaceKey';
	$optCapt[]='Remove #';
	$optUnit[]='';
	$optType[]='bool';
	$optExpl[]='Checked if the plugin have to remove the \'#\' tocken before the keywords.';
	
	$n++;
	$optName[]='replaceId';
	$optUnit[]='';
	$optCapt[]='Remove @';
	$optType[]='bool';
	$optExpl[]='Checked if the plugin have to remove the \'@\' tocken before twitter userid.';
	
	$n++;
	$optName[]='onTime';
	$optCapt[]='Print Time';
	$optUnit[]='second%s';
	$optType[]='num';
	$optExpl[]='Number of seconds the message will be visible at full opacity.';
	
	$n++;
	$optName[]='offTime';
	$optCapt[]='Transparent Time';
	$optUnit[]='second%s';
	$optType[]='num';
	$optExpl[]='Number of seconds the message box won\'t be visible.';
	
	
	$n++;
	$optName[]='fadeTime';
	$optCapt[]='Fade Time';
	$optUnit[]='second%s';
	$optType[]='num';
	$optExpl[]='Number of second the message will fade in or out.';
	
	$n++;
	$optName[$n]='maxTwitt';
	$optCapt[$n]='Maximum Messages';
	$optUnit[$n]='message%s';
	$optType[$n]='num';
	$optExpl[$n]='The maximum number of twitt the plugins will print.';
	$optParams[$n]['min']=1;
	$optParams[$n]['max']=20;


	
	// Get the Current Values
	for ($i=0; $i<count($optName); $i++) {
		$value=get_option('ccbSimpleTwitter-'.$optName[$i]);
		switch($optType[$i]) {
			case 'num':
				$optCurValue[$i]=floatval($value);
				break;
			case 'bool':
				$optCurValue[$i]=($value==1 || $value==true || $value=="1" || $value=='yes' || $value=='on' || $value=='true');
				break;
			default:
				$optCurValue[$i]=$value;
				break;
		}
	}
	
	// Option have been updated
	
	if (isset($_POST[ $hiddenName ]) && ($_POST[$hiddenName] == "Yep")) {
		for ($i=0; $i<count($optName); $i++) {
			if (isset($_POST['ccbSimpleTwitter-'.$optName[$i]]) || $optType[$i]=="bool") {
				$value=$_POST['ccbSimpleTwitter-'.$optName[$i]];
				switch($optType[$i]) {
					case 'num':
						$optUpdValue[$i]=floatval($value);
						if ($optUpdValue[$i] != $optCurValue[$i]) {
							update_option('ccbSimpleTwitter-'.$optName[$i], $optUpdValue[$i]);
							$optCurValue[$i]=$optUpdValue[$i];
							$message.=sprintf("The option '%s' has been set to %s %s.<br />\n",
									$optCapt[$i], $optUpdValue[$i], preg_replace('/%s/', $optUpdValue[$i]>1?"s":"", $optUnit[$i]));
						}
						break;
					case 'text':
						$optUpdValue[$i]=trim($value);
						if ($optUpdValue[$i] != $optCurValue[$i]) {
							update_option('ccbSimpleTwitter-'.$optName[$i], $optUpdValue[$i]);
							$optCurValue[$i]=$optUpdValue[$i];
							$message.=sprintf("The option '%s' has been set to %s.<br />\n",
									$optCapt[$i], $optUpdValue[$i]);
						}
						break;
					case 'bool':
						$optUpdValue[$i]=($value==1 || $value==true || $value=="1" || $value=='yes' || $value=='on' || $value=='true');
						if ($optUpdValue[$i] != $optCurValue[$i]) {
							update_option('ccbSimpleTwitter-'.$optName[$i], $optUpdValue[$i]);
							$optCurValue[$i]=$optUpdValue[$i];
							$message.=sprintf("The option '%s' has been set to %s.<br />\n",
									$optCapt[$i], $optUpdValue[$i]?"yes":"no");
						}
						break;
				}
			}
		}
	}

	if (isset($message) && strlen($message)>0) {
					echo "\n  <div class='updated' id='message'>\n";
					echo "    <p>".$message."</p>\n";
					echo "  </div>\n";
	}
	$nbCols=3;
	$wordpressPage=$_REQUEST['page'];
	echo "<h2>Simple Twitter Options</h2>\n";
	
	echo "<h4>If you think I diserve something for this plugins, press the donate button.</h4>\n";
?>	
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="P93N8UNZUTSCN">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
</form>
<?php
	
	echo "<h4>Settings</h4>\n";
	echo "<div class='form-wrap' style='width: 800px'>\n";
	echo "<form action='$wordpressPaage' method='post' name='simpleTwitterForm' id='simpleTwitterForm'>\n";
	echo "<table style='width: 100%; border-spacing: 10px'>\n";
	for ($i=0; $i<count($optName); $i++) {
		if ($i%$nbCols==0) echo "<tr>\n";
		echo "<td style='vertical-align: top; background-color: #eee;'>\n";
		printf("	<div class='form-field''>\n");
		printf("		<label for='ccbSimpleViewer-%s'>%s</label>", $optName[$i],$optCapt[$i]);
		switch($optType[$i]) {
			case "text":
				printf("\n		<input id='ccbSimpleViewer-%s' type='text' value='%s' name='ccbSimpleTwitter-%s'",
					$optName[$i], htmlentities($optCurValue[$i], ENT_QUOTES, get_settings('blog_charset')), $optName[$i]);
				if (isset($optParams[$i]) && count($optParams[$i])>0)
				foreach ($optParams[$i] as $key => $value) {
					switch ($key) {
						case "required": if ($value==true) printf(" aria-required='true'"); break;
						case "size": printf(" size='%d'", $value);
					}
				}
				printf(">\n");
				break;
			case "num":
				printf("<input type='text' onkeyup='javascript:this.value=this.value.replace(/[^0-9]/g, \"\");' name='ccbSimpleTwitter-%s' value='%d'",
					$optName[$i], $optCurValue[$i]);
				if (isset($optParams[$i]) && count($optParams[$i])>0)
				foreach ($optParams[$i] as $key => $value) {
					switch ($key) {
						case "required": if ($value==true) printf(" aria-required='true'"); break;
					}
				}
				printf(">\n");
				break;
			case "bool":
				printf("<input type='checkbox' name='ccbSimpleTwitter-%s' value='on' style='float: left; width: 20px;' %s", $optName[$i], $optCurValue[$i]?"CHECKED":"");
				if (isset($optParams[$i]) && count($optParams[$i])>0)
				foreach ($optParams[$i] as $key => $value) {
					switch ($key) {
						case "required": if ($value==true) printf(" aria-required='true'"); break;
					}
				}
				printf("><br />\n");
				break;
		}
		printf("		<p>%s</p>\n", $optExpl[$i]);
		printf("	</div>\n\n");
		echo "</td>\n";
		if ($i%$nbCols==($nbCols-1)) echo "</tr>\n";
	}
	if (count($optName)%$nbCols>0) {
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "<input type='hidden' value='Yep' name='$hiddenName'>\n";
	echo "<p class='submit'><input type='submit' name='submit' id='submit' class='button' value='Submit'  /></p>\n";
	echo "</form>\n\n";
	echo "</div>\n\n\n";
}



/* *************************************

		SET THE DEFAULT OPTIONS' VALUES

   ************************************* */

  


function chriscb_simpleTwitterActivation() {
	global $wpdb;
	// Default value
	
	if (!get_option('ccbSimpleTwitter-username'))
			update_option('ccbSimpleTwitter-username', '');
			
	if (!get_option('ccbSimpleTwitter-maxTwitt'))
			update_option('ccbSimpleTwitter-maxTwitt', 10);
			
	if (!get_option('ccbSimpleTwitter-replaceKey'))
			update_option('ccbSimpleTwitter-replaceKey', true);
		
	if (!get_option('ccbSimpleTwitter-replaceId'))
			update_option('ccbSimpleTwitter-replaceId', false);

	if (!get_option('ccbSimpleTwitter-onTime'))
			update_option('ccbSimpleTwitter-onTime', 5);

	if (!get_option('ccbSimpleTwitter-offTime'))
			update_option('ccbSimpleTwitter-offTime', 3);

	if (!get_option('ccbSimpleTwitter-fadeTime'))
			update_option('ccbSimpleTwitter-fadeTime', 2);
	
}

/* *************************************

		The filter that add the "Settings" link
		on the plugins list page.

   ************************************* */

function ccb_simpleTwitter_addSettingsLink($links, $file) {
	static $this_plugin;

	echo "\n<!--\n FILE : ".$file."\nPLUGIN_BASENAME : ".plugin_basename(__FILE__)."\n-->\n";

	if ($file == plugin_basename(__FILE__)){
		$settings_link = '<a href="admin.php?page=ccbSimpleTwitterAdminMenu">Settings</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}


/* *************************************

		Here is the important function, the one you have
		to add to your theme... It will display the twitts
		on your page.

   ************************************* */

function ccb_simpleTwitter() {
	global $wpdb;
?>
<script type="text/javascript">
	var twitterFeed=new Array();
<?php
	
	
	// First of all, we have to generate the javascript part...

	$userName=trim(get_option('ccbSimpleTwitter-username'));
	
	$feed= file_get_contents("http://twitter.com/statuses/user_timeline/".$userName.".rss");
	$xmlFeed = new SimpleXmlElement($feed);

	$n=0;
	foreach ($xmlFeed->channel->item as $entry) {
		$date=strtotime($entry->pubDate);
		$content=preg_replace("/".$userName."\: /", "", $entry->title);
		$link=$entry->link;
		
		$content=preg_Replace("/#([A-Za-z0-9_]+)/", "__twitter_keyword__\\1", $content);
		$content=preg_Replace("/@([A-Za-z0-9_]+)/", "__twitter_user__\\1", $content);
		
		$content=htmlentities($content, ENT_QUOTES,  get_settings('blog_charset'));
				
		$in=array(
				'`((?:https?|ftp)://\S+[[:alnum:]]/?)`si',
				'`((?<!//)(www\.\S+[[:alnum:]]/?))`si'
		);
		
		$out=array(
			'<span id="twitterLink"><a href="$1"  target="_blank" rel=nofollow>$1</a></span> ',
			'<span id="twitterLink"><a href="http://$1" target="_blank" rel="nofollow">$1</a></span> '
		);
		
		$content=preg_replace($in, $out, $content);

		$out=sprintf('<a href="http://twitter.com/#!/search?q=%%23\\1" target="_blank">%s\\1</a>', get_option('ccbSimpleTwitter-replaceKey')?'':'#');
		$content=preg_replace("/__twitter_keyword__([A-Za-z0-9_]+)/", $out, $content);
		$out=sprintf('<a href="http://twitter.com/#!/\\1" target="_blank">%s\\1</a>', get_option('ccbSimpleTwitter-replaceId')?'':'@');
		$content=preg_replace("/__twitter_user__([A-Za-z0-9_]+)/", $out, $content);

	
		
		printf("  twitterFeed[%d] = '<span id=\"twitterDate\">%s</span> • <span id=\"twitterUser\"><a href=\"http://twitter.com/#!/%s\" target=\"_blank\">@%s</a></span> • %s';\n", 
			$n,date("d.m.y", $date)."&nbsp;".date("H:i", $date), $userName, $userName,  $content);
		
		$n++;
		if ($n==10) break;
	}

?>
	var currentMessage=twitterFeed.length;
	var lastMessage=twitterFeed.length;
	
	var noMessageDuration=<?php echo 1000*get_option('ccbSimpleTwitter-offTime') ?>;
	var messageDuration=<?php echo 1000*get_option('ccbSimpleTwitter-onTime') ?>;
	var TimeToFade = <?php echo 1000*get_option('ccbSimpleTwitter-fadeTime') ?>;
	
	function twitterStartAnimation() {
		twitterFade("TwitterMessageBox");
		setTimeout("nextTwitterMessage('TwitterMessageBox')", TimeToFade+1);
		setTimeout("twitterFade('TwitterMessageBox')", noMessageDuration+TimeToFade);
		setTimeout("twitterStartAnimation()", noMessageDuration+(TimeToFade*2)+messageDuration);	
	}

	function nextTwitterMessage(eid) {
		currentMessage=currentMessage+1;
		if (currentMessage>=lastMessage) currentMessage=0;
		
		twitterBox=document.getElementById(eid); 
			
		twitterBox.innerHTML=twitterFeed[currentMessage];
	}
	
	
	
	function twitterFade(eid)
	{
		var element = document.getElementById(eid);
		if(element == null)
			return;
   
		if(element.FadeState == null)
		{
			if(element.style.opacity == null
				|| element.style.opacity == ''
				|| element.style.opacity == '1') {
				element.FadeState = 2;
			} else {
				element.FadeState = -2;
			}
		}

		if(element.FadeState == 1 || element.FadeState == -1) {
			element.FadeState = element.FadeState == 1 ? -1 : 1;
			element.FadeTimeLeft = TimeToFade - element.FadeTimeLeft;
		} else {
			element.FadeState = element.FadeState == 2 ? -1 : 1;
    	element.FadeTimeLeft = TimeToFade;
    	setTimeout("twitterAnimateFade(" + new Date().getTime() + ",'" + eid + "')", 33);
		}  
	}
	
	function twitterAnimateFade(lastTick, eid) {
  	var curTick = new Date().getTime();
  	var elapsedTicks = curTick - lastTick;
 
  	var element = document.getElementById(eid);
 
  	if(element.FadeTimeLeft <= elapsedTicks)
  	{
    	element.style.opacity = element.FadeState == 1 ? '1' : '0';
    	element.style.filter = 'alpha(opacity = '
        	+ (element.FadeState == 1 ? '100' : '0') + ')';
    			element.FadeState = element.FadeState == 1 ? 2 : -2;
    			return;
  	}
 
		element.FadeTimeLeft -= elapsedTicks;
		var newOpVal = element.FadeTimeLeft/TimeToFade;
		if(element.FadeState == 1)
 			newOpVal = 1 - newOpVal;

		element.style.opacity = newOpVal;
		element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
 
		setTimeout("twitterAnimateFade(" + curTick + ",'" + eid + "')", 33);
	}


	

</script>

<?php
	// And add the box the page code
?>

<div id='TwitterMessageBox' onload='nextTwitterMessage();'>
<script type="text/javascript">
	twitterStartAnimation();
</script>
</div>
<?php
	
}


?>