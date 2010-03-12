<?php
/**
 * @package WordPress
 * @subpackage Mad Mimi for WordPress
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

	add_action( 'widgets_init', 'madmimi_load_widget' );
	
	function madmimi_load_widget() {
		register_widget( 'MadMimiWidget' );
	}
	
	
	class MadMimiWidget extends WP_Widget {
	 
	    function MadMimiWidget() {
	    	$widget_options = array('description'=>'Add a Mad Mimi form to your and start adding to your lists!', 'classname' => 'madmimi');
	        parent::WP_Widget(false, $name = 'Mad Mimi Signup Form', $widget_options);
	    }
	 
	 
	    function widget($args, $instance) {      
	    	
	    	$args = wp_parse_args( $args, $instance );
	    	$output = '';
	        extract( $args );
	        
	        if($hide != 'yes') {
	      
					$output .= $before_widget;
					$output .= $before_title . $title . $after_title;
						
							$output .= "\n\t".$this->mimi_signup_form($args)."\n\t";
						 
					$output .= $after_widget;
			        $output = apply_filters('mad_mimi_signup_form_widget', $output); // Since 1.1
			        
		         echo $output;
			} // end if hide
	    }
	 	private function r($content, $echo=true) {
	 			$output = '<pre>';
	 			$output .= print_r($content, true); //print_r(mixed expression [, bool return])
	 			$output .= '</pre>';
	 		if($echo) {	echo $output; }
	 		else { return $output; }
	 	}
	 	function mimi_signup_form($args, $shortcode_id = false, $show_title = false) {
			if($shortcode_id) { $this->number = (int)$shortcode_id;}
			$error = $out = '';
			$link =true;
			extract( $args );
			
			if($hide == 'yes' && $show_title && $show_title != "false" && $show_title != "0") {
				$out .= "<h2 class='mad_mimi_title'>$title</h2>";
			}
			
			if(isset($_POST['success']) && isset($_POST['mimi_form_id']) && $_POST['mimi_form_id'] == $this->number) { // The form has been submitted
				if($_POST['success'] == 1) { // It worked
					$out .= wpautop($successmessage); // Added wpautop() 1.2
					$link = false; // Added 1.2
				} else { // Didn't work
					## Need error message
					$out .= '<p class="madmimi_error">There was an error with the signup process.</p>';
				}
			} else { // Not been submitted
				if(
					isset($_POST['signuperror']) 
					&& $_POST['mimi_form_id'] == $this->number // Make sure it's this form, not another Mad Mimi form.
				) { $error = '<p class="madmimi_error">'.$_POST['signuperror'].'</p>';}
						
				$out .= "<form method='post' id='mad_mimi_form{$this->number}'>
					<div>";
				
				$error = apply_filters('mad_mimi_signup_form_error', $error); // Since 1.1
				$out .= $error;
				
				if($signup_name)  { $out .=	"		<label for='signup_name{$this->number}'>Name</label><br /><input id='signup_name{$this->number}' name='signup[name]' type='text' value='{$_POST[signup][name]}' /><br />"; }
				if($signup_phone)  { $out .=	"		<label for='signup_phone{$this->number}'>Phone</label><br /><input id='signup_phone{$this->number}' name='signup[phone]' type='text' value='{$_POST[signup][phone]}' /><br />"; }
				if($signup_company)  { $out .=	"		<label for='signup_company{$this->number}'>Company</label><br /><input id='signup_company{$this->number}' name='signup[company]' type='text' value='{$_POST[signup][company]}' /><br />"; }
				if($signup_title)  { $out .=	"		<label for='signup_title{$this->number}'>Title</label><br /><input id='signup_title{$this->number}' name='signup[title]' type='text' value='{$_POST[signup][title]}' /><br />"; }
				if($signup_address)  { $out .=	"		<label for='signup_address{$this->number}'>Address</label><br /><input id='signup_address{$this->number}' name='signup[address]' type='text' value='{$_POST[signup][address]}' /><br />"; }
				if($signup_city)  { $out .=	"		<label for='signup_city{$this->number}'>City</label><br /><input id='signup_city{$this->number}' name='signup[city]' type='text' value='{$_POST[signup][city]}' /><br />"; }
				if($signup_state)  { $out .=	"		<label for='signup_state{$this->number}'>State</label><br /><input id='signup_state{$this->number}' name='signup[state]' type='text' value='{$_POST[signup][state]}' /><br />"; }
				if($signup_zip)  { $out .=	"		<label for='signup_zip{$this->number}'>Zip</label><br /><input id='signup_zip{$this->number}' name='signup[zip]' type='text' value='{$_POST[signup][zip]}' /><br />"; }
				if($signup_country)  { $out .=	"		<label for='signup_country{$this->number}'>Country</label><br /><input id='signup_country{$this->number}' name='signup[country]' type='text' value='{$_POST[signup][country]}' /><br />"; }
				$out .=	"		<label for='signup_email{$this->number}'>Email<span class='required' title='This field is required'>*</span></label><br /><input id='signup_email{$this->number}' name='signup[email]' type='text' value='{$_POST[signup][email]}' /><br />";
				
				$out .=	"		<input name='commit' class='button' type='submit' value='$submittext' />";
				if(!empty($successredirect)) { 
					$successredirect = urlencode($successredirect);  
					$out .= "<input type='hidden' name='signup[redirect]' value='$successredirect' />";
				}

				$out .= '<input type="hidden" id="mimi_form_id" name="mimi_form_id" value="'.$this->number.'" />';
				
				$out .= $this->mimi_signup_lists($args);
				
				$out .=	"</div>
				</form>";
			}
			if($link && $madmimi_link) {  // Since 1.1, please help support the plugin author by leaving this code intact :-)
				$out .= '<p class="mad_mimi_link">Emails by <a href="http://bit.ly/mad-mimi" title="Mad Mimi is a simple, intelligent and powerful email marketing utility that anyone can use." >Mad Mimi</a></p>';
			}
			$out = apply_filters('mad_mimi_signup_form', $out); // Since 1.1
	 		return $out;
	 	}
	 	
	 	function mimi_signup_lists($args) {
	 		$lists = array(); 
	 		$out = '';
	 		
	 		$out .= '<input name="signup[list_name]" value="';
	 		foreach($args as $arg => $value) {
				preg_match('/list\-(.+)/', $arg, $matches);
				if(!empty($matches[0])) {
					$lists[] = $matches[1];
				}	
			}
			if(is_array($lists)) { $lists = @implode(',',$lists); } // @ Since 1.1 to prevent error
			else { $lists = ''; } // Since 1.1, updated 1.2 to blank instead of false
			
			$out .= $lists.'" type="hidden" />';
			return $out;
	 	}
	 	
	    function update($new_instance, $old_instance) {
			$new_instance['initiated'] = true;
	       	return $new_instance;
	    }
	 
	    function form($instance) {
	    	
	        $title = esc_attr($instance['title']);
	        $formcode = $instance['formcode'];
	        $inputsize = $instance['inputsize'];
	        if(is_int($this->number) || !$this->number) { $madmimi_number = $this->number; echo '<p><strong>Mad Mimi Widget ID='.$madmimi_number.'</strong></p>'; } else { $madmimi_number = '#';}
	        if(isset($instance['initiated'])) { $initiated = true; } else { $initiated=false;}

	        mimi_show_configuration_check();
	        
	        ?>
	        	<p>You can embed the form in post or page content by using the following code: <code>[madmimi id=<?php echo $madmimi_number; ?>]</code>. <?php if($madmimi_number == '#') { ?><small>(The ID will show once the widget is saved for the first time.)</small><?php } ?></p>
	        	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
	            <?php echo $error; ?>
	            
	             <?php 
	             	$listsList = $this->create_user_lists_list($instance, 'list'); 
	             	if($listsList) { ?>
			             <fieldset style="border:1px solid #ccc; margin-bottom:1em;">
			             	<legend style="font-size:1.2em; text-align:center;"><h4>Select User Lists for this Form</h4></legend>
			             	<p style="padding:.5em 1em 0;">When filling out the form, users will be added to the following lists. If none selected, they will be added to your general Audience list.</p>
				             <?php echo $listsList; ?>
			             </fieldset>
	             <?php } ?>
	             
	             <fieldset style="border:1px solid #ccc; margin-bottom:1em;">
	             	<legend style="font-size:1.2em; text-align:center;"><h4>Show the Following Fields</h4></legend>
	             	<?php $this->create_form_fields_list($instance); ?>
	             </fieldset>
	            <?php 
	            	$this->madmimi_make_textfield($initiated, true, '', $instance['successredirect'], $this->get_field_id('successredirect'),$this->get_field_name('successredirect'), 'Optional subscription confirmation page (for after the contact enters their details and submits the form). <strong>Must be a complete URL</strong>, including <code>http://</code>'); 
	            	 
					$this->madmimi_make_textarea($initiated, true, 'You have been added to our list. Thank you for signing up.', $instance['successmessage'], $this->get_field_id('successmessage'),$this->get_field_name('successmessage'), 'Message shown on successful signup (in place of the form). <small>(HTML allowed)</small>'); 
	            	
	            	$this->madmimi_make_textfield($initiated, true, 'Submit', $instance['submittext'], $this->get_field_id('submittext'),$this->get_field_name('submittext'), 'Change Submit Button Text'); ?>
	            	<p><?php $this->madmimi_make_checkbox($instance['hide'], $this->get_field_id('hide'),$this->get_field_name('hide'), 'Do not display widget in sidebar.<br /><small>If you are exclusively using the [madmimi id='.$madmimi_number.'] shortcode, not the sidebar widget. Note: you can use a widget in <em>both</em> sidebar and shortcode at the same time.</small>'); ?></p>
	            	
	            	<p><?php $this->madmimi_make_checkbox($instance['madmimi_link'], $this->get_field_id('madmimi_link'),$this->get_field_name('madmimi_link'), 'Display a link to Mad Mimi&nbsp;<img src="'.get_bloginfo('url').'/'.WPINC.'/images/smilies/icon_biggrin.gif" width="15" height="15" alt="Tanks a miwwiuns!" title="Yer relly nice, tanks yu!" /><br /><small>This link takes users to MadMimi.com, letting them know where emails will be coming from. The link also helps support the plugin author when someone clicks it.</small>'); ?></p><?php
	    }
	function create_form_fields_list($instance) {
		$out = '<ul style="margin-left:1.5em;padding-top:.5em">';
		$out .=			'<li>'.$this->madmimi_get_checkbox($instance['signup_name'], $this->get_field_id('signup_name'),$this->get_field_name('signup_name'), 'Name');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_phone'], $this->get_field_id('signup_phone'),$this->get_field_name('signup_phone'), 'Phone');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_company'], $this->get_field_id('signup_company'),$this->get_field_name('signup_company'), 'Company');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_title'], $this->get_field_id('signup_title'),$this->get_field_name('signup_title'), 'Title');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_address'], $this->get_field_id('signup_address'),$this->get_field_name('signup_address'), 'Address');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_city'], $this->get_field_id('signup_city'),$this->get_field_name('signup_city'), 'City');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_state'], $this->get_field_id('signup_state'),$this->get_field_name('signup_state'), 'State');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_zip'], $this->get_field_id('signup_zip'),$this->get_field_name('signup_zip'), 'Zip');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_country'], $this->get_field_id('signup_country'),$this->get_field_name('signup_country'), 'Country');
		$out .= '</li>'.'<li>'.$this->madmimi_get_checkbox($instance['signup_email'], $this->get_field_id('signup_email'),$this->get_field_name('signup_email'), 'Email', true,true);
		$out .= '</ul>';
		echo $out;
	}
	function create_user_lists_list($instance, $type = 'checkbox') {
		#global $instance;
		$dropdown = $list = $xml = false;
		if($type == 'dropdown') { $dropdown = true; }
		if($type == 'list') { $list = true; }
		
		$response = get_user_lists();
	
		if(function_exists('simplexml_load_string') && 1 ==2) {
			$xml = simplexml_load_string($response);
		} else { // Since 1.2
			echo madmimi_make_notice_box('<strong>This plugin requires PHP5 for user list management</strong>. Your web host does not support PHP5.<br /><br />Everything else should work in the plugin except for being able to define what lists a user will be added to upon signup.<br /><br /><strong>You may contact your hosting company</strong> and ask if they can upgrade your PHP version to PHP5; generally this is done at no cost.');
		}
				
		if($xml && is_object($xml) && sizeof($xml->list) > 0) { // Updated 1.2
			if($dropdown) { $out = '<select>'; }
			if($list)  { $out = '<ul style="margin-left:1.5em;">'; }
			foreach($xml->list as $l) {
				$a = $l->attributes();
				
				if($dropdown) { 
					$out .= $this->mimi_create_option($a['name'],$a['id']);
				} else {
					#$out .= $this->madmimi_make_checkbox('list', $a['name'],$a['id']);
					if($list)  {$out .= '<li>'; }
					$out .= $this->madmimi_get_checkbox($instance['list-'.strtolower($a['name'])], $this->get_field_id('list-'.strtolower($a['name'])),$this->get_field_name('list-'.strtolower($a['name'])), $a['name'], $a['name']);
					if($list)  {$out .= '</li>'; }
				}
			}
			if($list)  { $out .= '</ul>'; }
			if($dropdown) { $out .= '</select>'; }
			return $out;
		} else {
			return false;
		}
	}
	
	function mimi_create_checkbox($id, $name, $value, $label='') {
		return "<label for='$id'>$label<input id='$id' name='$id\[\]' type='checkbox' value='$value' />$name</label>\n";
	}
	
	function mimi_create_option($name, $value) {
		return "<option value='$value'>$name</option>\n";
	}
	     
	function madmimi_make_textfield($initiated = false, $required=false, $default, $setting = '', $fieldid = '', $fieldname='', $title = '') {
		
		if(!$initiated || ($required && empty($setting))) { $setting = $default; }
	    
		$input = '
		<p>
			<label for="'.$fieldid.'">'.__($title).'
			<input type="text" class="widefat" id="'.$fieldid.'" name="'.$fieldname.'" value="'.$setting.'"/>
			</label>
		</p>';
		
		echo $input;
	}
	function madmimi_make_textarea($initiated = false, $required=false, $default, $setting = '', $fieldid = '', $fieldname='', $title = '') {
		
		if(!$initiated || ($required && empty($setting))) { $setting = $default; }
	    
		$input = '
		<p>
			<label for="'.$fieldid.'">'.__($title).'
			<textarea class="widefat" id="'.$fieldid.'" name="'.$fieldname.'" cols="40" rows="5">'.$setting.'</textarea>
			</label>
		</p>';
		
		echo $input;
	}
	    
	function madmimi_make_checkbox($setting = '', $fieldid = '', $fieldname='', $title = '', $value = 'yes', $checked = false, $disabled = false) {
		echo $this->madmimi_get_checkbox($setting, $fieldid, $fieldname, $title, $value, $checked,$disabled);
	}
	function madmimi_get_checkbox($setting = '', $fieldid = '', $fieldname='', $title = '', $value = 'yes', $checked = false, $disabled = false) {
		$checkbox = '
			<input type="checkbox" id="'.$fieldid.'" name="'.$fieldname.'" value="'.$value.'"';
				if($checked || !empty($setting)) { $checkbox .= ' checked="checked"'; }
				if($disabled)  { $checkbox .= ' disabled="disabled"';}
				$checkbox .= ' class="checkbox" />
			<label for="'.$fieldid.'">'.__($title).'</label>';
	    return $checkbox;
	}	
	
} // End Class

	function madmimi_show_form($atts) {
		global $post; // prevent before content
			$atts = extract(shortcode_atts(array('id' => '1', 'title' => false), $atts));
			if(!is_admin()) { 
				$shortcode_id = $id;
				$settings = get_option('widget_madmimiwidget');
				$form = new MadMimiWidget();
				return $form->mimi_signup_form($settings[$id], $shortcode_id, $title);
			} // get sidebar settings, echo finalcode
	}

	add_shortcode('MadMimi', 'madmimi_show_form');
	add_shortcode('madmimi', 'madmimi_show_form');
	
?>