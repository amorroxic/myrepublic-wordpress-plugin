<?php
/*
Plugin Name: my*republic Plugin
Plugin URI: http://www.myrepublic.org
Description: integrating myrepublic into blogs
Version: 0.1
Author: Adrian Mihai 
Author URI: http://www.nouauzina.ro/


*/

define("PLAYER_WIDTH", mrp_get_value("player_width")); // default width
define("PLAYER_HEIGHT", mrp_get_value("player_height")); // default height
define("PLAYER_REGEXP", "/\[myrepublic ([[:print:]]+)\]/");
define("PLAYER_CODE", "<div style='border:5px solid #000; width:".PLAYER_WIDTH."px;height:".PLAYER_HEIGHT."px;'><object classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0' id='cities' width='".PLAYER_WIDTH."' height='".PLAYER_HEIGHT."' align='middle'><param name='allowScriptAccess' value='always' /><param name='allowFullScreen' value='true' /><param name='FlashVars' value='###PARAMS###'/><param name='movie' value='http://myrepublic.org/flash/cities.swf' /><param name='quality' value='high' /><param name='bgcolor' value='#000000' /><embed src='http://myrepublic.org/flash/cities.swf' quality='high' bgcolor='#000000' width='".PLAYER_WIDTH."' height='".PLAYER_HEIGHT."' swLiveConnect=true id='cities' name='cities' align='middle' allowFullScreen='true' allowScriptAccess='always' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' FlashVars='###PARAMS###'/></object></div>");
																																																																																					
function myrepublic_plugin_callback($match) {
	$output = PLAYER_CODE;
	$tag_parts = explode(" ", rtrim($match[1], "]"));
	$params = implode("&",$tag_parts);
	$output = str_replace("###PARAMS###", $match[1], $output);
	return ($output);
}

function myrepublic_plugin($content) {
	return preg_replace_callback(PLAYER_REGEXP, 'myrepublic_plugin_callback', $content);
}

add_filter('the_content', 'myrepublic_plugin');
add_filter('the_content_rss', 'myrepublic_plugin');
add_filter('comment_text', 'myrepublic_plugin');
add_filter('the_excerpt', 'myrepublic_plugin');


$default_options = array(

	"player_width" 		=> 	array("title"=>"Player Width","type"=>"","default"=>"640"),
	"player_height" 	=>  array("title"=>"Player Height","type"=>"","default"=>"480"),
	"feed_url" 			=>  array("title"=>"Feed URL","type"=>"","default"=>"http://www.myrepublic.org")

);


add_action('admin_menu', 'myrepublic_menu');
add_action('admin_head', 'myrepublic_head');
add_action('wp_print_scripts','myrepublic_js');

function myrepublic_menu() {

	global $default_options;
	
  	add_menu_page('my*republic', 'my*republic', 'administrator', 'my-republic', 'myrepublic_form');
  	
	foreach ($default_options as $key=>$value) {
		if ( !get_option( $key, $value["default"] ) ) {
			add_option( $key, $value["default"] );
		}
	}
	
	if ( isset($_POST['saved'])) {
	
		foreach ($default_options as $key => $value) {

				$dbKey = get_option( $key );
				$keyValue = $_POST[$key];
				
				if (!$dbKey) add_option($key,$keyValue);
				if ($keyValue == "") {
					delete_option($key);
				} else {
					update_option($key, $keyValue );
				}
		}
		
	 } 
	 
}

function myrepublic_form() {

	global $section;

	if ( isset( $_REQUEST['saved'] ) ) echo '<div id="message" class="updated fade"><p><strong>'.__('Options saved.').'</strong></p></div>';
	$formAction = "admin.php?page=my-republic";//str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);

?>
	<div class="wrap" id="a">
	<h2>my*republic player configuration</h2>
	</div>
	<br/>
	<form action="<?php echo $formAction; ?>" method="post">
	
		<input name="saved" type="hidden" value="1"/>
		<input name="section" type="hidden" value="<?php echo $section;?>"/>

				
	<table class="widefat" style="width:700px;">

	<tr valign="top">
		<th scope="row" style="width:400px;"><label for="player_width">Player Width</label></th> 
		<td style="width:450px;"> 
			<input name="player_width" type="text"/>
		</td> 
	</tr> 

	<tr valign="top">
		<th scope="row" style="width:400px;"><label for="player_height">Player Height</label></th> 
		<td style="width:450px;"> 
			<input name="player_height" type="text"/>
		</td> 
	</tr> 

	<tr valign="top">
		<th scope="row" style="width:400px;"><label for="feed_url">Feed URL</label></th> 
		<td style="width:450px;"> 
			<input name="feed_url" type="text" size="40"/>
		</td> 
	</tr> 

	</table>
	<br/>
	<input type="submit" name="Submit" class="button-primary" value="&nbsp;&nbsp;Save Changes&nbsp;" />

	</form>
				

<?php
}


function myrepublic_js() {
	wp_enqueue_script("jquery");
	wp_enqueue_script("jquery-ui-core");
	wp_enqueue_script("jquery-ui-tabs");
}

function myrepublic_head()
{
	global $default_options;
	
?>

	<script>
		
		jQuery(document).ready(function() {
		
			<?php
				foreach ($default_options as $key => $value) {
					if ($value["type"] != "image") {
						echo "jQuery('[name=".$key."]').val('".mrp_get_value($key)."');\n";
					}
				}
			?>
			
			if (jQuery('#message')) {
				jQuery('#message').animate({opacity:0.0},1025).hide("fast");
			}
			
		});
			
	</script>


<?php	
	
}

function mrp_get_value($keyName) {

	global $default_options;
	$keyValue = get_option( $keyName , $default_options[$keyName]["default"]);
	if (!$keyValue) {
		return $default_options[$keyName]["default"];
	} else {
		return $keyValue;
	}
	
}

?>