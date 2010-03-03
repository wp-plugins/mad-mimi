<?php 
/*
Plugin Name: Mad Mimi for WordPress
Plugin URI: http://www.seodenver.com/mad-mimi/
Description: Add a Mad Mimi signup form to your WordPress website.
Author: Katz Web Services, Inc.
Version: 1.0
Author URI: http://katzwebservices.com
*/

/*
Copyright 2010 Katz Web Services, Inc.  (email: info@katzwebservices.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

@include('madmimi-widget.php');

add_action('admin_menu', 'kws_mad_mimi_admin');

function kws_mad_mimi_admin() {
    add_options_page('Mad Mimi', 'Mad Mimi', 'administrator', 'mad-mimi', 'mad_mimi_page');  
}

add_filter( 'plugin_action_links', 'kws_mad_mimi_settings_link', 10, 2 );

function kws_mad_mimi_settings_link( $links, $file ) {
	static $this_plugin;
	 if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
	if ( $file == $this_plugin ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=mad-mimi' ) . '">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}

add_action('plugins_loaded', 'madmimi_register_widgets');
function madmimi_register_widgets(){

	if (!function_exists('register_sidebar_widget')) {
		return;
	}
	register_sidebar_widget( 'Mad Mimi', 'madmimi_display_widget');
}

function madmimi_shortcode($atts){
	madmimi_display_widget();
}
add_shortcode('madmimi_widget', 'madmimi_display_widget');



global $user, $api, $ty;
add_option('mad_mimi_username', '');
add_option('mad_mimi_api', '');
add_option('ty_page', '');
$api = get_option('mad_mimi_api');
$user = get_option('mad_mimi_username');
$ty = get_option('mad_mimi_ty_page');

function mad_mimi_page() {
	?>
<div class="wrap">
	<h2>Mad Mimi for WordPress</h2>
	<div class="postbox-container" style="width:65%;">
		<div class="metabox-holder">	
			<div class="meta-box-sortables">
				<form action="options.php" method="post">
					<?php wp_nonce_field('update-options'); ?>
				<?php 
					mimi_show_configuration_check(false);
					
					$rows[] = array(
							'id' => 'mad_mimi_username',
							'label' => 'Mad Mimi Username',
							'content' => "<input type='text' name='mad_mimi_username' id='mad_mimi_username' value='".esc_attr(get_option('mad_mimi_username'))."' size='40' />",
							'desc' => 'Your Mad Mimi username (your account email address)'
						);
						
					$rows[] = array(
							'id' => 'mad_mimi_api',
							'label' => 'Mad Mimi API Key',
							'desc' => 'Find your API Key at <a href="https://madmimi.com/user/edit" target="_blank">https://madmimi.com/user/edit</a>',
							'content' => "<input type='text' name='mad_mimi_api' id='mad_mimi_api' value='".esc_attr(get_option('mad_mimi_api'))."' size='40' />"
						);
							
					kws_mad_mimi_postbox('madmimisettings','Mad Mimi Settings', kws_mad_mimi_form_table($rows), false); 
					
				?>
					
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="<?php foreach($rows as $row) { $output .= $row['id'].','; } echo substr($output, 0, -1);?>" />
					<input type="hidden" name="action" value="update" />
					<p class="submit">
					<input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>" />
					</p>
				</form>
			</div>
		</div>
	</div>
	<div class="postbox-container" style="width:34%;">
		<div class="metabox-holder">	
			<div class="meta-box-sortables">
			<?php kws_mad_mimi_postbox('madmimihelp','Setting Up Your Form', kws_mad_mimi_configuration(), true);  ?>
			</div>
		</div>
	</div>
	
</div>
<?php
	
	
	#create_user_lists_list();
	
}

function kws_mad_mimi_configuration() {
$out = <<<EOD

<h4>Shortcode Use</h4>
<ul>
<li><code>id</code> : The ID of the <a href="widgets.php">Mad Mimi widget</a>. Each Mad Mimi widget will show you the <strong>Mad Mimi Widget ID</strong> at the top of the form.</li>
<li><code>title</code> : Whether to show the widget title; true or false. Default: false. use <code>title=true</code> to show.</li>
</ul>
<h4>Sample code:</h4>
<p><code>[madmimi id=3 title=true]</code></p>
<p>The form generated by Mad Mimi widget ID #3 will display and show the title.</p>
<h4>Alternate uses</h4>
<ul style="list-style:disc outside; margin-left:2em;">
<li>You can use <code>&lt;?php echo madmimi_show_form(array('id'=&gt;3, 'title'=>true)); ?&gt;</code> in your template code instead of the shortcode below.</li>
<li>You can also use <code>&lt;?php echo do_shortcode('[madmimi id=3 title=true]'); ?&gt;</code> if you would like.</li>
<li>Shortcodes work in text widgets; you can add a form to any text widget using the shortcodes.</li>
</ul>

EOD;
return $out;
}

function mimi_show_configuration_check($link = true) {
	if(madmimi_check_settings()) {
    	echo '<div
    	style="
background-color: rgb(255, 255, 224);
border-color: rgb(230, 219, 85);
-webkit-border-bottom-left-radius: 3px 3px;
-webkit-border-bottom-right-radius: 3px 3px;
-webkit-border-top-left-radius: 3px 3px;
-webkit-border-top-right-radius: 3px 3px;
border-style: solid;
border-width: 1px;
margin: 5px 0px 15px;
padding: 0px 0.6em;
"
    	><p
    	style="line-height: 1;
margin: 0.5em 0px;
padding: 2px;">Your '; if($link) { echo '<a href="' . admin_url( 'options-general.php?page=mad-mimi' ) . '">'; } echo  __('Mad Mimi account settings'); if($link) { echo '</a>'; } echo ' are configured properly. You\'re ready to go.'; if(!$link) { echo ' <strong><a href="widgets.php">Configuring your forms</a>.</strong>'; } echo '</p></div>';
    } else {
    	
    	echo '<div
    	style="
background-color: rgb(255, 235, 232);
border-color: rgb(204, 0, 0);
-webkit-border-bottom-left-radius: 3px 3px;
-webkit-border-bottom-right-radius: 3px 3px;
-webkit-border-top-left-radius: 3px 3px;
-webkit-border-top-right-radius: 3px 3px;
border-style: solid;
border-width: 1px;
margin: 5px 0px 15px;
padding: 0px 0.6em;
"
    	><p
    	style="line-height: 1;
margin: 0.5em 0px;
padding: 2px;">Your '; if($link) { echo '<a href="' . admin_url( 'options-general.php?page=mad-mimi' ) . '">'; } echo  __('Mad Mimi account settings') ; if($link) { echo '</a>'; } echo '  are <strong>not configured properly</strong>.</p></div>';
    };
}

function madmimi_process_submissions() {
	global $mm_debug;
	if(!is_admin()) {
		if($mm_debug) { echo '<pre style="text-align:left;">'.print_r($_POST, true).'</pre>'; }
		if(isset($_POST['signup'])) {
			if(is_email($_POST['signup']['email'])) {
				$lists = $_POST['signup']['list_name'];
				$lists = explode(',',$lists);
				foreach($lists as $list) {
					add_users_to_list(array($_POST['signup']),$list);
				}
			} else {
				$_POST['signuperror'] = 'The email you entered is not valid.';
			}
		}
	}
}
add_action('init', 'madmimi_process_submissions',1);

/*
*
* This redirects the user if there is a valid redirect page set up
* in the widget settings. If not, wp_redirect just doesn't do anything.
* For some reason, it needs to be set up as it is; $_POST logic is screwing it up.
*
*/
function madmimi_redirect_preventer() {
	if(!is_email($_POST['signup']['email'])) { 
		return false;
	} else {
		return urldecode($_POST['signup']['redirect']);
	}	
}
function madmimi_redirect() {
	wp_redirect(madmimi_redirect_preventer());
}
add_action('plugins_loaded','madmimi_redirect');


function get_user_lists() {
	global $api, $user, $ty;
	
	$url = 'http://madmimi.com/audience_lists/lists.xml?username='.$user.'&api_key='.$api;
	#CURLOPT_URL
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$response = curl_exec($ch);
	curl_close($ch);

	return $response;
}



function process_emails($signup, $list = false) {
	global $mm_debug;
	$i = 0;
	if(empty($signup)||!$signup) { return false; }
	if(!is_array($signup)){ $signup = array($signup); }

	foreach($signup as $s) {
		if(is_email($s['email'])) {
			if($i == 0) { $csv_data = "name,phone,company,title,address,city,state,zip,email,add_list\n"; }
			
			$csv_data .= '"';
			if(isset($s['name'])) {		$csv_data .= htmlentities($s['name']);	}	$csv_data .= '","';
			if(isset($s['phone'])) {	$csv_data .= htmlentities($s['phone']);	}	$csv_data .= '","';
			if(isset($s['company'])) {	$csv_data .= htmlentities($s['company']);}	$csv_data .= '","';
			if(isset($s['title'])) {	$csv_data .= htmlentities($s['title']);	} 	$csv_data .= '","';
			if(isset($s['address'])) { 	$csv_data .= htmlentities($s['address']); }	$csv_data .= '","';
			if(isset($s['city'])) {		$csv_data .= htmlentities($s['city']);	}	$csv_data .= '","';
			if(isset($s['state'])) {	$csv_data .= htmlentities($s['state']);	}	$csv_data .= '","';
			if(isset($s['zip'])) {		$csv_data .= htmlentities($s['zip']);	}	$csv_data .= '","';
			$csv_data .= "{$s['email']}\",";
			$csv_data .= '"'.$list.'"';
			$csv_data .= "\n";
			$i++;
		}
	}
	if($mm_debug) { 
		echo '<pre>'.print_r($csv_data,true).'</pre>'; 
	}
	
	if($i > 0) {
		$_POST['success'] = true;
		return $csv_data;
	} else {
		$_POST['success'] = false;
		return false;
	}
}

function madmimi_check_settings($emails=false, $list=false) {
	global $api, $user, $ty;
	
	$url = 'http://madmimi.com/audience_lists/lists.xml?username='.$user.'&api_key='.$api;
	#CURLOPT_URL
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$response = curl_exec($ch);
	curl_close($ch);
	
	$response = simplexml_load_string($response);
	
	if(is_object($response)) {
		return true;
	} else {
		return false;
	}
	
	
}

function add_users_to_list($signup=false, $list=false) {
	global $api, $user, $ty;
	
	$csv_data = process_emails($signup,$list); 
	
	$ch = curl_init('http://madmimi.com/audience_members');
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
	'username='.$user.'&api_key='.$api.'&csv_file='.$csv_data);
	$response = curl_exec($ch);
}

// THANKS JOOST!
function kws_mad_mimi_form_table($rows) {
	$content = '<table class="form-table" width="100%">';
	foreach ($rows as $row) {
		$content .= '<tr><th valign="top" scope="row" style="width:50%">';
		if (isset($row['id']) && $row['id'] != '')
			$content .= '<label for="'.$row['id'].'" style="font-weight:bold;">'.$row['label'].':</label>';
		else
			$content .= $row['label'];
		if (isset($row['desc']) && $row['desc'] != '')
			$content .= '<br/><small>'.$row['desc'].'</small>';
		$content .= '</th><td valign="top">';
		$content .= $row['content'];
		$content .= '</td></tr>'; 
	}
	$content .= '</table>';
	return $content;
}

function kws_mad_mimi_postbox($id, $title, $content, $padding=false) {
	?>
		<div id="<?php echo $id; ?>" class="postbox">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class="hndle"><span><?php echo $title; ?></span></h3>
			<div class="inside" <?php if($padding) { echo 'style="padding:10px; padding-top:0;"'; } ?>>
				<?php echo $content; ?>
			</div>
		</div>
	<?php
}


?>