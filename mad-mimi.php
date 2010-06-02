<?php 
/*
Plugin Name: Mad Mimi for WordPress
Plugin URI: http://www.seodenver.com/mad-mimi/
Description: Add a Mad Mimi signup form to your WordPress website.
Author: Katz Web Services, Inc.
Version: 1.3
Author URI: http://www.katzwebservices.com
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

@include('madmimi-widget.php'); // Updated 1.2.1


class KWSMadMimi {

    function KWSMadMimi() {
    
        add_action('admin_menu', array(&$this, 'admin'));
        add_filter( 'plugin_action_links', array(&$this, 'settings_link'), 10, 2 );
        add_action('plugins_loaded', array(&$this, 'register_widgets'));
        add_action('init', array(&$this, 'process_submissions'),1);
        add_action('plugins_loaded', array(&$this, 'redirect'));
        add_action('admin_init', array(&$this, 'settings_init') );
        
        $options = get_option('madmimi', array());
        
        // Upgrade options from previous versions of this plugin:
        if ( (!isset($options['version']) || $options['version'] < 1) ) {
            $options['version'] = 1;
            $oldUser = get_option('mad_mimi_username', false);
            
            if ($oldUser !== false) {
                $options['username'] = $oldUser;
                $options['api'] = get_option('mad_mimi_api');
                
            }            
            delete_option('mad_mimi_username');
            delete_option('mad_mimi_api');
            delete_option('mad_mimi_ty_page');
            update_option('madmimi', $options);
        }
        $this->api = $options['api'];
        $this->username = $options['username'];
        $this->new_users_list = isset($options['new_users_list']) ? $options['new_users_list'] : 0;

        // Lets do this check only once
        $this->settings_checked = $this->check_settings();
        
        // and put this in a global too, so widgets can check it
        global $madmimi_settings_checked;
        $madmimi_settings_checked = $this->settings_checked;
        
        if ((int) $this->new_users_list > 0) {
            add_action('user_register', array(&$this, 'user_register') );
        }
        
        
        // First thing at init
        $this->process_submissions();
    
    }
    
    function settings_init() {
        register_setting( 'madmimi_options', 'madmimi', array(&$this, 'sanitize_settings') );
    }
    
    function admin() {
        add_options_page('Mad Mimi', 'Mad Mimi', 'administrator', 'mad-mimi', array(&$this, 'admin_page'));  
    }
    
    function settings_link( $links, $file ) {
        static $this_plugin;
        if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
        if ( $file == $this_plugin ) {
            $settings_link = '<a href="' . admin_url( 'options-general.php?page=mad-mimi' ) . '">' . __('Settings', 'mad-mimi') . '</a>';
            array_unshift( $links, $settings_link ); // before other links
        }
        return $links;
    }
    
    function admin_page() {
        ?>
        <div class="wrap">
        <h2>Mad Mimi for WordPress</h2>
        <div class="postbox-container" style="width:65%;">
            <div class="metabox-holder">	
                <div class="meta-box-sortables">
                    <form action="options.php" method="post">
                        <?php settings_fields('madmimi_options'); ?>
                    <?php 
                        $this->show_configuration_check(false);
                        
                        if(function_exists('curl_init')) { // Added 1.2.2
                        $rows[] = array(
                                'id' => 'mad_mimi_username',
                                'label' => 'Mad Mimi Username',
                                'content' => "<input type='text' name='madmimi[username]' id='mad_mimi_username' value='".esc_attr($this->username)."' size='40' />",
                                'desc' => 'Your Mad Mimi username (your account email address)'
                            );
                            
                        $rows[] = array(
                                'id' => 'mad_mimi_api',
                                'label' => __('Mad Mimi API Key', 'mad-mimi'),
                                'desc' => sprintf(__('Find your API Key at %s'),'<a href="https://madmimi.com/user/edit" target="_blank">https://madmimi.com/user/edit</a>'),
                                'content' => "<input type='text' name='madmimi[api]' id='mad_mimi_api' value='".esc_attr($this->api)."' size='40' />"
                            );
                                
                        $this->postbox('madmimisettings',__('Mad Mimi Settings', 'mad-mimi'), $this->form_table($rows), false);
                        
                        if ($this->settings_checked) {
                            
                            $lists = madmimi_get_user_lists();
                            
                            if(function_exists('simplexml_load_string')) {
                                $xml = simplexml_load_string($lists);
                            } else { // Since 1.2
                                echo madmimi_make_notice_box(__('<strong>This plugin requires PHP5 for user list management</strong>. Your web host does not support PHP5.<br /><br />Everything else should work in the plugin except for being able to define what lists a user will be added to upon signup.<br /><br /><strong>You may contact your hosting company</strong> and ask if they can upgrade your PHP version to PHP5; generally this is done at no cost.', 'mad-mimi'));
                            }
                            
                            $SelList = array();
                            $SelList[$this->new_users_list] = ' selected';
                            
                            if($xml && is_object($xml) && sizeof($xml->list) > 0) { // Updated 1.2
                                $listsSelect = '<select name="madmimi[new_users_list]">'; 
                                $listsSelect .= '<option value="0"' . $SelList[0] . '>'.__('Do not add new users to MadMimi Audience', 'mad-mimi').'</option>';
                                
                                foreach($xml->list as $l) {
                                    $a = $l->attributes();
                                    $listsSelect .= '<option value="' . $a['id'] . '"' . $SelList[(int)$a['id']] . '>' . $a['name'] . '</option>';
                                }

                                $listsSelect .= '</select>';
                            } 

                            $settings_auto_import[] = array(
                                    'id' => 'mad_mimi_autoimport',
                                    'label' => __('Sync Users', 'mad-mimi'),
                                    'content' => $listsSelect,
                                    'desc' => __('When a user is added or register him/herself in your blog, add also to your MadMimi audience list.', 'mad-mimi')
                                );
                            
                            $this->postbox('madmimisettings_newusers',__('New Users', 'mad-mimi'), $this->form_table($settings_auto_import), false);
                        
                    }
                        
                    ?>
                        
                        <input type="hidden" name="action" value="update" />
                        <input type="hidden" name="page_options" value="<?php foreach($rows as $row) { $output .= $row['id'].','; } echo substr($output, 0, -1);?>" />
                        <input type="hidden" name="action" value="update" />
                        <p class="submit">
                        <input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes', 'mad-mimi') ?>" />
                        </p>
                    <?php } ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="postbox-container" style="width:34%;">
            <div class="metabox-holder">	
                <div class="meta-box-sortables">
                <?php $this->postbox('madmimihelp',__('Setting Up Your Form', 'mad-mimi'), $this->configuration(), true);  ?>
                </div>
            </div>
        </div>
        
    </div>
    <?php
        
        
        #create_user_lists_list();
        
    }
    
    function sanitize_settings($input) {
        return $input;
    }
    
    function configuration() {
        $out = __('<h4>Shortcode Use</h4>
        <ul>
        <li><code>id</code> : The ID of the <a href="widgets.php">Mad Mimi widget</a>. Each Mad Mimi widget will show you the <strong>Mad Mimi Widget ID</strong> at the top of the form.</li>
        <li><code>title</code> : Whether to show the widget title; true or false. Default: false. use <code>title=true</code> to show.</li>
        </ul>
        <h4>Sample code:</h4>
        <p><code>[madmimi id=3 title=true]</code></p>
        <p>The form generated by Mad Mimi widget ID #3 will display and show the title.</p>
        <h4>Alternate uses</h4>
        <ul style="list-style:disc outside; margin-left:2em;">
        <li>You can use <code>&lt;?php echo madmimi_show_form(array(\'id\'=&gt;3, \'title\'=>true)); ?&gt;</code> in your template code instead of the shortcode below.</li>
        <li>You can also use <code>&lt;?php echo do_shortcode(\'[madmimi id=3 title=true]\'); ?&gt;</code> if you would like.</li>
        <li>Shortcodes work in text widgets; you can add a form to any text widget using the shortcodes.</li>
        </ul>', 'mad-mimi');
        return $out;
    }
    
    
    function show_configuration_check($link = true) {
        if(!function_exists('curl_init')) { // Added 1.2.2
            $content = __('Your server does not support <code>curl_init</code>. Please call your host and ask them to enable this functionality, which is required for this awesome plugin.', 'mad-mimi');
            echo $this->make_notice_box($content, 'error');
        } else {
            if($this->settings_checked) {
                $content = __('Your '); if($link) { $content .= '<a href="' . admin_url( 'options-general.php?page=mad-mimi' ) . '">'; } $content .=  __('Mad Mimi account settings', 'mad-mimi'); if($link) { $content .= '</a>'; } $content .= __(' are configured properly. You\'re ready to go.'); if(!$link) { $content .= __(' <strong><a href="widgets.php">Configuring your forms</a>.</strong>', 'mad-mimi'); } 
                echo $this->make_notice_box($content, 'success');
            } else {
                $content = 'Your '; if($link) { $content .= '<a href="' . admin_url( 'options-general.php?page=mad-mimi' ) . '">'; } $content .=  __('Mad Mimi account settings', 'mad-mimi') ; if($link) { $content .= '</a>'; } $content .= '  are <strong>not configured properly</strong>.';
                echo $this->make_notice_box($content, 'error');
            };
        }
    }

    function make_notice_box($content, $type="error") {
        $output = '';
        if($type!='error') { $output .= '<div style="background-color: rgb(255, 255, 224);border-color: rgb(230, 219, 85);-webkit-border-bottom-left-radius: 3px 3px;-webkit-border-bottom-right-radius: 3px 3px;-webkit-border-top-left-radius: 3px 3px;-webkit-border-top-right-radius: 3px 3px;border-style: solid;border-width: 1px;margin: 5px 0px 15px;padding: 0px 0.6em;">';
        } else {
            $output .= '<div style="background-color: rgb(255, 235, 232);border-color: rgb(204, 0, 0);-webkit-border-bottom-left-radius: 3px 3px;-webkit-border-bottom-right-radius: 3px 3px;-webkit-border-top-left-radius: 3px 3px;-webkit-border-top-right-radius: 3px 3px;border-style: solid;border-width: 1px;margin: 5px 0px 15px;padding: 0px 0.6em;">';
        }
        $output .= '<p style="line-height: 1; margin: 0.5em 0px; padding: 2px;">'.$content.'</div>';
        return($output);
    }

    function process_submissions() {
        global $mm_debug;
        if(!is_admin()) {
            if($mm_debug) { echo '<pre style="text-align:left;">'.print_r($_POST, true).'</pre>'; }
            if(isset($_POST['signup'])) {
                if(is_email($_POST['signup']['email'])) {
                    if(isset($_POST['signup']['list_name'])) { // Added 1.1
                        $lists = $_POST['signup']['list_name'];
                        $lists = explode(',',$lists);
                        foreach($lists as $list) {
                            $this->add_users_to_list(array($_POST['signup']),$list);
                        }
                    } else { // Added 1.1 - lists aren't required anyway
                        $this->add_users_to_list(array($_POST['signup']));
                    }
                } else {
                    if(empty($_POST['signup']['email'])) {
                        $_POST['signuperror'] = 'Please enter your email address.';
                    } else {
                        $_POST['signuperror'] = 'The email you entered is not valid.';
                    }
                }
            }
        }
    }
    
    /*
    *
    * This redirects the user if there is a valid redirect page set up
    * in the widget settings. If not, wp_redirect just doesn't do anything.
    * For some reason, it needs to be set up as it is; $_POST logic is screwing it up.
    *
    */
    function redirect_preventer() {
        if(!is_email($_POST['signup']['email'])) { 
            return false;
        } else {
            return urldecode($_POST['signup']['redirect']);
        }	
    }
    function redirect() {
        wp_redirect($this->redirect_preventer());
    }
    
    
    
    function process_emails($signup, $list = false) {
        global $mm_debug;
        $i = 0;
        if(empty($signup)||!$signup) { return false; }
        if(!is_array($signup)){ $signup = array($signup); }

        foreach($signup as $s) {
            if(is_email($s['email'])) {
                $add_list = 'add_list'; if($list) { $add_list = 'add_list'; }  // Added 1.2 // Added 1.2
                if($i == 0) { $csv_data = "name,phone,company,title,address,city,state,zip,email,$add_list\n"; }
                
                $csv_data .= '"';
                if(!empty($s['name']) && isset($s['name'])) {		$csv_data .= htmlentities($s['name']);	}	$csv_data .= '","';
                if(!empty($s['phone']) && isset($s['phone'])) {	$csv_data .= htmlentities($s['phone']);	}	$csv_data .= '","';
                if(!empty($s['company']) && isset($s['company'])) {	$csv_data .= htmlentities($s['company']);}	$csv_data .= '","';
                if(!empty($s['title']) && isset($s['title'])) {	$csv_data .= htmlentities($s['title']);	} 	$csv_data .= '","';
                if(!empty($s['address']) && isset($s['address'])) { 	$csv_data .= htmlentities($s['address']); }	$csv_data .= '","';
                if(!empty($s['city']) && isset($s['city'])) {		$csv_data .= htmlentities($s['city']);	}	$csv_data .= '","';
                if(!empty($s['state']) && isset($s['state'])) {	$csv_data .= htmlentities($s['state']);	}	$csv_data .= '","';
                if(!empty($s['zip']) && isset($s['zip'])) {		$csv_data .= htmlentities($s['zip']);	}	$csv_data .= '","';
                $csv_data .= "{$s['email']}\",";
                if($list) { $csv_data .= '"'.$list.'"'; } // Added 1.2
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

    function check_settings() {
        $reponse = '';
        $url = 'http://madmimi.com/audience_lists/lists.xml?username='.$this->username.'&api_key='.$this->api;
        
        if(function_exists('curl_init')) { // Added 1.2.2
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($ch);
            curl_close($ch);
        } 
        if(!$response || !function_exists('curl_init')) {  // Added 1.2.2
            echo $this->make_notice_box(__('Your web host does not support <code>curl</code>, which this plugin requires for <strong>list management functionality</strong>. Please contact your host and see if they can activate <code>curl</code>; generally this is possible and done at no cost.', 'mad-mimi'));
            return false;
        }
        if(!function_exists('simplexml_load_string')) { // Added 1.2
            echo $this->make_notice_box(__('Your web host does not support PHP5, which this plugin requires for <strong>list management functionality</strong>. Please contact your host and see if they can upgrade your PHP version; generally this is done at no cost.', 'mad-mimi'));
            if($response) {
                return true;
            } 
            return false;
        } else {
            $response = @simplexml_load_string($response);  // Added @ 1.2.2
            if(is_object($response)) {
                return true;
            } else {
                return false;
            }
        }
        
        
    }

    function add_users_to_list($signup=false, $list=false) {
        if(function_exists('curl_init')) { // Added 1.2.2	
            $csv_data = $this->process_emails($signup,$list); 
            $ch = curl_init('http://madmimi.com/audience_members');
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
            'username='.$this->username.'&api_key='.$this->api.'&csv_file='.$csv_data);
            $response = curl_exec($ch);
        }
    }

    // THANKS JOOST!
    function form_table($rows) {
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

    function postbox($id, $title, $content, $padding=false) {
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
    
    
    function user_register($user_id) {
        if (!$this->settings_checked)
            return false;
        
        global $wpdb;
        
        $email = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE ID = $user_id");
        
        if (!$email)
            return false;
        
        $ch = curl_init('http://madmimi.com/audience_lists/' . $this->new_users_list . '/add');
		curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_MUTE, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'username='.$this->username.'&api_key='.$this->api.'&email='.$email);
		
        //prevents output
        ob_start();
        curl_exec($ch);
        ob_end_clean();
        
    }
    
}


add_action('init', 'madmimi_initialize',1);

function madmimi_initialize() {
    new KWSMadMimi();
 
 	$plugin_dir = basename(dirname(__FILE__)).'languages';
	load_plugin_textdomain( 'mad-mimi', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
	
}

function madmimi_get_user_lists() {
	$options = get_option('madmimi');
    $api = $options['api'];
    $user = $options['username'];
	
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


?>
