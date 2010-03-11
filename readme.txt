=== Mad Mimi for WordPress ===
Tags: madmimi, mad mimi, widget, newsletter, form, signup, newsletter widget, newsletter plugin, newsletters, emails, email, email newsletter form, newsletter form, newsletter signup, email widget, email marketing, newsletter, form, signup
Requires at least: 2.8
Tested up to: 2.9.2
Stable tag: trunk
Contributors: katzwebdesign
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=Mad%20Mimi%20for%20WordPress&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8

Add a Mad Mimi signup form to your WordPress website in the content or the sidebar.

== Description ==

<h3>Add Mad Mimi signup form to your WordPress website.</h3>

This plugin adds a newsletter signup form to your website in the content and the sidebar of your site.

<h4>Plugin Features:</h4>
* Select which forms users subscribe to
* Unlimited signup forms for any number of email lists
* Choose to include a number of fields in the form, including:
	* Email
	* Name
	* Phone
	* Company
	* Title
	* Address
	* City
	* State
	* ZIP
	* Country

<em>Note: this plugin requires a Mad Mimi account. <a href="bit.ly/mad-mimi" rel="nofollow" title="Visit MadMimi.com for a free account">Sign up for a free account here</a>.</em>

== Installation ==

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
1. Activate the plugin
1. Go to the plugin settings page (under Settings > Mad Mimi)
1. Enter in your Mad Mimi Username (the account's email address) and the API key. (Find your API Key at <a href="https://madmimi.com/user/edit" target="_blank" rel="nofollow">https://madmimi.com/user/edit</a>)
1. Click Save Changes.
1. If the settings are correct, a link will appear to the widgets page (Appearance > Widgets). Follow it.
1. Drag the Mad Mimi Signup Form widget to a sidebar, and configure the form.
1. If you want the form to be embedded in content, instead of shown in the sidebar, check the checkbox for "Do not display widget in sidebar", then follow the instructions for inserting the shortcode into your content where you would like the form to be displayed.

== Screenshots ==

1. How the widget appears in the Widgets panel 
2. How the signup form appears in the default WP sidebar.
3. How submissions appear on the Mad Mimi Audience page

== Frequently Asked Questions == 

= What is the plugin license? =

* This plugin is released under a GPL license.

= What is Mad Mimi? =
Mad Mimi is the easiest email marketing tool out there. It's built from the ground up to be simple and usable. <a href="bit.ly/mad-mimi" rel="nofollow" title="Learn more about Mad Mimi at MadMimi.com">Learn more about Mad Mimi</a>.

= Do I need a Mad Mimi account to use this plugin? =
Yes, this plugin requires a Mad Mimi account. <a href="bit.ly/mad-mimi" rel="nofollow">Sign up for a free account here</a>.


= How do I use the new `apply_filters()` functionality? (Added 1.1) =
If you want to change some code in the widget, you can use the WordPress `add_filter()` function to achieve this.

You can add code to your theme's `functions.php` file that will modify the widget output. Here's an example:
<pre>
function my_example_function($widget) { 
	// The $widget variable is the output of the widget
	// This will replace 'this word' with 'that word' in the widget output.
	$widget = str_replace('this word', 'that word', $widget);
	// Make sure to return the $widget variable, or it won't work!
	return $widget;
}
add_filter('mad_mimi_signup_form', 'my_example_function');
</pre>

You can also modify the error message by hooking into `mad_mimi_signup_form_error` in a similar manner.

== Changelog ==

= 1.1 =
* For those experiencing the `implode()` fatal error (it even *sounds* bad!), this update should fix it thanks to an updated `mimi_signup_lists()` function in `madmimi_widget.php`.
* Added error message check to make sure the error message displays on the form that was submitted, not another Mad Mimi form.
* Added checks for whether or not there are any lists, and if not, add the contact to the All Audience List
* Added three hooks for `add_filter()`: `mad_mimi_signup_form` modifies the form if used by shortcode or in the widget, `mad_mimi_signup_form_widget` modifies the widget output (including before and after the form), and `mad_mimi_signup_form_error` modifies the error message. Refer to the FAQ for more information.
* Updated widget display
= 1.0 =
* Initial plugin release

== Upgrade Notice ==

= 1.1 = 
* This update should fix the `implode()` fatal error for those experiencing that error
* Adds `add_filter()` functionality. If you don't know what that is, don't worry about it :-)

= 1.0 = 
* Blastoff!
