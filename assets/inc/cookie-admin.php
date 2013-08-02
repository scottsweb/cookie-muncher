<?php

/***************************************************************
* Functions cookie_muncher_hide_plugin
* Stop this plugin checking for updates at WordPress.org - http://toggl.es/t3ezCT
***************************************************************/

add_filter( 'http_request_args', 'cookie_muncher_hide_plugin', 5, 2 );

function cookie_muncher_hide_plugin( $r, $url ) {
	
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
		return $r; // Not a plugin update request. Bail immediately.
	
	$plugins = unserialize( $r['body']['plugins'] );
	unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
	unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
	$r['body']['plugins'] = serialize( $plugins );
	return $r;
}

/***************************************************************
* Functions cookie_muncher_defaults & cookie_muncher_delete options
* Register defaults and clean up on plugin uninstall
***************************************************************/

register_activation_hook(COOKIE_MUNCH_FILE, 'cookie_muncher_defaults');
register_uninstall_hook(COOKIE_MUNCH_FILE, 'cookie_muncher_delete_options');

function cookie_muncher_defaults() {
	
	$tmp = get_option('cookie_muncher_options');
	
    if (!is_array($tmp)) {
		$arr = array(
			"cm_mode" => 'optout',	
			"cm_euonly" => 1,
			"cm_adverts" => 0,
			"cm_message" => __("This website would like to store cookies on your computer. We use cookies to make the site and your experience better.", 'cm'),
			"cm_link_page" => "",
			"cm_link_text" => __("Find out more &raquo;", 'cm'),
			"cm_button_hide" => 0,
			"cm_button_text" => __("Decline Cookies", 'cm'),
			"cm_position" => "topRight",
			"cm_timeout" => "60000",
			"cm_background_colour" => "#ffffff",
			"cm_text_colour" => "#000000",
			"cm_link_colour" => "#0000EE",
			"cm_link_hover_colour" => "#551A8B"
		);
		update_option('cookie_muncher_options', $arr);
		$tmp = $arr;
	}
}

function cookie_muncher_delete_options() {

	delete_option('cookie_muncher_options');
	
}

/***************************************************************
* Functions cookie_muncher_plugin_row_meta
* Add some handy shortcuts to the plugin admin screen
***************************************************************/

add_filter('plugin_row_meta', 'cookie_muncher_plugin_row_meta', 10, 2);

function cookie_muncher_plugin_row_meta($links, $file) {
	
	if ($file ==  COOKIE_MUNCH_BASE) {

		$links[] = '<a href="'.get_admin_url().'options-general.php?page=cookie-settings">' . __('Settings', 'cm') . '</a>';
		$links[] = '<a href="http://scott.ee/">' . __('Support', 'cm') . '</a>';
		$links[] = '<a href="http://twitter.com/scottsweb">' . __('Twitter','cm') . '</a>';
		
	}

	return $links;
}

/***************************************************************
* Functions: lots for settings page
* Create an administration settings page within WordPress
***************************************************************/

add_action( 'admin_enqueue_scripts', 'cookie_muncher_admin_scripts' );

function cookie_muncher_admin_scripts($hook) {
	
	if ('settings_page_cookie-settings' != $hook)
		return;

	wp_enqueue_script('cookie_scripts', COOKIE_MUNCH_JS_URL.'/admin.js', array('farbtastic'), '1.3' );
	wp_enqueue_style('farbtastic');
}

add_action('admin_menu', 'cookie_muncher_menu');

function cookie_muncher_menu() {

	global $pagenow, $cookie_muncher_options_page;

 	$cookie_muncher_options_page = add_options_page(__('EU Cookie Muncher','cm'), __('Cookie Muncher', 'cm'), 'administrator', 'cookie-settings', 'cookie_settings');
 	
 	if ($cookie_muncher_options_page) add_action("load-$cookie_muncher_options_page",'cookie_muncher_help_screen');
}

function cookie_muncher_help_screen() {
	
	global $cookie_muncher_options_page;

	$screen = get_current_screen();
    
    if ($screen->id != $cookie_muncher_options_page)
    	return;

	$screen->add_help_tab(
		array(
	        'id'      => 'cookie_muncher-tips',
	        'title'   => __('Tips','cm'),
	        'callback' => 'cookie_muncher_tips_screen',
	    ) 
    );
}

function cookie_muncher_tips_screen() {
?>
	<p><?php _e('To get the most out of this plugin take note of the following tips:', 'cm'); ?></p>
	
	<ul>
		<li><?php _e('<strong>Message:</strong> Keep the notification message short and link through to a deeper page detailing cookie use.','cm'); ?></li>
		<li><?php _e('<strong>Position:</strong> Ensure the notification position does not cover crucial areas of your site.','cm'); ?></li>
		<li><?php _e('<strong>Colour:</strong> Ensure there is plenty of contrast between your colour choices for better usability.','cm'); ?></li>
		<li><?php _e('<strong>CSS:</strong> Copy the /assets/css/cookie_muncher.css file to your theme folder to completely customise the look and feel.','cm'); ?></li>
		<li><?php _e('<strong>Shortcode:</strong> Use the <strong>[cookie_button enable_text="Cookies ON" disable_text="Cookies OFF"]</strong> shortcode on your policy page to allow visitors to change their settings.', 'cm'); ?></li>
	</ul>
<?php
}

add_action('admin_init', 'cookie_muncher_register_setting');

function cookie_muncher_register_setting() {
	
	register_setting( 'cookie-muncher-settings-group', 'cookie_muncher_options', 'cookie_muncher_validate');

}

function cookie_muncher_validate($input) {
	
	global $allowedtags;
		
	if ($input['cm_mode'] != ("optin" || "optout")) { $input['cm_mode'] = "optout"; }
	if (isset($input['cm_euonly'])) $input['cm_euonly'] = absint($input['cm_euonly']);
	if (isset($input['cm_adverts'])) $input['cm_adverts'] = absint($input['cm_adverts']);
	//if (isset($input['cm_message']) && ($input['cm_message'] != "")) $input['cm_message'] = wp_kses($input['cm_message'], $allowedtags); // seem so to be striping all HTML
	$input['cm_link_text'] = sanitize_text_field($input['cm_link_text']);
	if (isset($input['cm_button_hide'])) $input['cm_button_hide'] = absint($input['cm_button_hide']);
	$input['cm_button_text'] = sanitize_text_field($input['cm_button_text']);
	$input['cm_position'] = sanitize_text_field($input['cm_position']);
	$input['cm_timeout'] = absint($input['cm_timeout']);
	if (!preg_match('/^#[a-f0-9]{6}$/i', $input['cm_background_colour'])) $input['cm_background_colour'] = '#ffffff';
	if (!preg_match('/^#[a-f0-9]{6}$/i', $input['cm_text_colour'])) $input['cm_text_colour'] = '#000000';
	if (!preg_match('/^#[a-f0-9]{6}$/i', $input['cm_link_colour'])) $input['cm_link_colour'] = '#0000EE';
	if (!preg_match('/^#[a-f0-9]{6}$/i', $input['cm_link_hover_colour'])) $input['cm_link_hover_colour'] = '#551A8B';
	
	return $input;
	
}

function cookie_settings() { 
		
	$version = get_bloginfo('version');

?>

<!-- flattr js -->
<script type="text/javascript">

	/* <![CDATA[ */
	    (function() {
	        var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
	        s.type = 'text/javascript';
	        s.async = true;
	        s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
	        t.parentNode.insertBefore(s, t);
	    })();
	/* ]]> */
	
</script>

<style type="text/css">
	.FlattrButton { position: relative; top: 3px !important; }
	.scottsweb-credit { padding: 10px 10px 10px 10px; background: #f1f1f1; border-bottom: 1px solid #e3e3e3; overflow: hidden; -webkit-border-radius: 5px; -moz-border-radius: 5px; -khtml-border-radius: 5px; border-radius: 5px; }
	.scottsweb-credit img { float: left;  padding: 10px 11px 10px 5px; margin: 5px 5px 0 0; border-right: 1px solid #aaaaaa; }
	.scottsweb-credit p { float: left; padding: 5px 0; margin: 0 0 0 8px;}
</style>

<div class="wrap">
	
	<div class="icon32" id="icon-options-general"></div>
	
	<h2><?php _e('EU Cookie Muncher', 'cm'); ?></h2>	
		
	<h3><?php _e('General', 'cm'); ?></h3>

	<form method="post" action="options.php">
	    <?php 
	    settings_fields('cookie-muncher-settings-group');
	    $options = get_option('cookie_muncher_options');
		?>	  
  
		<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e( 'Mode', 'cm'); ?></th>
				<td>
					<select name="cookie_muncher_options[cm_mode]" id="cm_mode">
						<option value="optout" <?php if ($options['cm_mode'] == 'optout') { ?>selected="selected"<?php } ?>><?php _e('Opt Out (implied consent)','cm'); ?></option>
						<option value="optin" <?php if ($options['cm_mode'] == 'optin') { ?>selected="selected"<?php } ?>><?php _e('Opt In','cm'); ?></option>
					</select>
					<span class="description"><?php _e('Make the user opt in or out to cookies.','cm'); ?></span>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><?php _e( 'Apply to visitors in the EU only', 'cm'); ?></th>
				<td>
					<label><input name="cookie_muncher_options[cm_euonly]" type="checkbox" value="1" <?php if (isset($options['cm_euonly'])) { checked('1', $options['cm_euonly']); } ?> /> <span class="description"><?php _e('Will attempt to determine current visitors location based on IP address.','cm'); ?></span></label><br /> 
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Block Adverts', 'cm'); ?></th>
				<td>
					<label><input name="cookie_muncher_options[cm_adverts]" type="checkbox" value="1" <?php if (isset($options['cm_adverts'])) { checked('1', $options['cm_adverts']); } ?> /> <span class="description"><?php _e('Remove adverts from your site (e.g. Google Adsense) until cookies are accepted.','cm'); ?></span></label><br /> 
				</td>
			</tr>
				
			<tr>
				<th scope="row"><?php _e('Message', 'cm'); ?></th>
				<td>
					<input type="text" size="60" name="cookie_muncher_options[cm_message]" value="<?php if (isset($options['cm_message'])) { echo esc_attr($options['cm_message']); } ?>" />
					<span class="description"><?php _e('What would you like your message to say? HTML is supported.','cm'); ?></span>
				</td>
			</tr>
	
			<tr>
				<th scope="row"><?php _e('Link to Page', 'cm'); ?></th>
				<td>
					<?php if (isset($options['cm_link_page'])) { ?>
						<?php wp_dropdown_pages('name=cookie_muncher_options[cm_link_page]&show_option_none=-&selected='.$options['cm_link_page']); ?>
					<?php } else { ?>
						<?php wp_dropdown_pages('name=cookie_muncher_options[cm_link_page]&show_option_none=-'); ?>
					<?php } ?>
					<span class="description"><?php _e('Choose a page where you provide more information about your cookie policy.','cm'); ?></span>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php _e('Link Text', 'cm'); ?></th>
				<td>
					<input type="text" name="cookie_muncher_options[cm_link_text]" value="<?php if (isset($options['cm_link_text'])) { echo esc_attr($options['cm_link_text']); } ?>" />
					<span class="description"><?php _e('Link text for the page chosen above.','cm'); ?></span>
				</td>
			</tr>
			
			<?php if ($options['cm_mode'] == 'optout') { ?>
			<tr id="cm_button_hide_row">
				<th scope="row"><?php _e('Hide Button', 'cm'); ?></th>
				<td>
					<label><input name="cookie_muncher_options[cm_button_hide]" id="cm_button_hide" type="checkbox" value="1" <?php if (isset($options['cm_button_hide'])) { checked('1', $options['cm_button_hide']); } ?> /> <span class="description"><?php _e('In opt out mode you may wish to hide the button from the popup. If hidden use the shortcode <strong>[cookie_button]</strong> on your cookie policy page.','cm'); ?></span></label><br /> 
				</td>
			</tr>
			<?php } ?>
			
			<tr id="cm_button_text_row">
				<th scope="row"><?php _e('Button Text', 'cm'); ?></th>
				<td>
					<input type="text" name="cookie_muncher_options[cm_button_text]" id="cm_button_text" value="<?php if (isset($options['cm_button_text'])) { echo esc_attr($options['cm_button_text']); } ?>" />
					<span class="description"><?php _e('Text for the "accept cookies" or "decline cookies" button.','cm'); ?></span>
				</td>
			</tr>
		
		</table>
		
		<h3><?php _e('Design', 'cm'); ?></h3>
		
		<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e( 'Notification Position', 'cm' ); ?></th>
				<td>
					<select name="cookie_muncher_options[cm_position]">
						<option value="topLeft" <?php if ($options['cm_position'] == 'topLeft') { ?>selected="selected"<?php } ?>><?php _e('Top Left','cm'); ?></option>
						<option value="topRight" <?php if ($options['cm_position'] == 'topRight') { ?>selected="selected"<?php } ?>><?php _e('Top Right','cm'); ?></option>
						<option value="bottomLeft" <?php if ($options['cm_position'] == 'bottomLeft') { ?>selected="selected"<?php } ?>><?php _e('Bottom Left','cm'); ?></option>
						<option value="bottomRight" <?php if ($options['cm_position'] == 'bottomRight') { ?>selected="selected"<?php } ?>><?php _e('Bottom Right','cm'); ?></option>
						<option value="top" <?php if ($options['cm_position'] == 'top') { ?>selected="selected"<?php } ?>><?php _e('Top (Full Width)','cm'); ?></option>
						<option value="bottom" <?php if ($options['cm_position'] == 'bottom') { ?>selected="selected"<?php } ?>><?php _e('Bottom (Full Width)','cm'); ?></option>
					</select>
					<span class="description"><?php _e('Where on screen the screen should the notification appear?','cm'); ?></span>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Notification Timeout', 'cm' ); ?></th>
				<td>
					<select name="cookie_muncher_options[cm_timeout]">
						<option value="0" <?php if ($options['cm_timeout'] == '0') { ?>selected="selected"<?php } ?>><?php _e('Indefinately','cm'); ?></option>
						<option value="5000" <?php if ($options['cm_timeout'] == '5000') { ?>selected="selected"<?php } ?>><?php _e('5 Seconds','cm'); ?></option>
						<option value="10000" <?php if ($options['cm_timeout'] == '10000') { ?>selected="selected"<?php } ?>><?php _e('10 Seconds','cm'); ?></option>
						<option value="15000" <?php if ($options['cm_timeout'] == '15000') { ?>selected="selected"<?php } ?>><?php _e('15 Seconds','cm'); ?></option>
						<option value="20000" <?php if ($options['cm_timeout'] == '20000') { ?>selected="selected"<?php } ?>><?php _e('20 Seconds','cm'); ?></option>						
						<option value="30000" <?php if ($options['cm_timeout'] == '30000') { ?>selected="selected"<?php } ?>><?php _e('30 Seconds','cm'); ?></option>
						<option value="60000" <?php if ($options['cm_timeout'] == '60000') { ?>selected="selected"<?php } ?>><?php _e('60 Seconds','cm'); ?></option>
					</select>
					<span class="description"><?php _e('How long should the notification remain on screen?','cm'); ?></span>
				</td>
			</tr>
			
			<?php if (file_exists(STYLESHEETPATH.'/cookie_muncher.css')) { ?>
				<tr valign="top">
					<td colspan="2">
						<p><?php _e('Custom colour options are being overridden by <strong>cookie_muncher.css</strong> in your theme directory.', 'cm'); ?></p>
					</td>
				</tr>
			<?php } else { ?>
			
			<tr valign="top">
				<th scope="row"><?php _e( 'Notification Background Colour', 'cm' ); ?></th>
				<td>
					<fieldset><legend class="screen-reader-text"><span><?php _e('Notification Background Colour', 'cm'); ?></span></legend>
						<input type="text" name="cookie_muncher_options[cm_background_colour]" id="cm_background_colour" value="<?php echo esc_attr($options['cm_background_colour']); ?>" />
						<input type="button" class="pickcolor button hide-if-no-js" id="cm_background_colour_button" value="<?php esc_attr_e('Select a Colour', 'cm'); ?>" />
						<div id="cm_background_colour_picker" class="cm_colour_picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Notification Font Colour', 'cm' ); ?></th>
				<td>
					<fieldset><legend class="screen-reader-text"><span><?php _e('Notification Font Colour', 'cm'); ?></span></legend>
						<input type="text" name="cookie_muncher_options[cm_text_colour]" id="cm_text_colour" value="<?php echo esc_attr($options['cm_text_colour']); ?>" />
						<input type="button" class="pickcolor button hide-if-no-js" id="cm_text_colour_button" value="<?php esc_attr_e('Select a Colour', 'cm'); ?>" />
						<div id="cm_text_colour_picker" class="cm_colour_picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
					</fieldset>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><?php _e( 'Notification Link Colour', 'cm' ); ?></th>
				<td>
					<fieldset><legend class="screen-reader-text"><span><?php _e('Notification Link Colour', 'cm'); ?></span></legend>
						<input type="text" name="cookie_muncher_options[cm_link_colour]" id="cm_link_colour" value="<?php echo esc_attr($options['cm_link_colour']); ?>" />
						<input type="button" class="pickcolor button hide-if-no-js" id="cm_link_colour_button" value="<?php esc_attr_e('Select a Colour', 'cm'); ?>" />
						<div id="cm_link_colour_picker" class="cm_colour_picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Notification Link Hover Colour', 'cm' ); ?></th>
				<td>
					<fieldset><legend class="screen-reader-text"><span><?php _e('Notification Link Hover Colour', 'cm'); ?></span></legend>
						<input type="text" name="cookie_muncher_options[cm_link_hover_colour]" id="cm_link_hover_colour" value="<?php echo esc_attr($options['cm_link_hover_colour']); ?>" />
						<input type="button" class="pickcolor button hide-if-no-js" id="cm_link_hover_colour_button" value="<?php esc_attr_e('Select a Colour', 'cm'); ?>" />
						<div id="cm_link_hover_colour_picker" class="cm_colour_picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
					</fieldset>
				</td>
			</tr>
			
			<?php } ?>
		
		</table>
			    
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'cm') ?>" />
		</p>
	</form>
	
	<div class="scottsweb-credit">
		<a href="http://scott.ee" title="Scott Evans - Web Designer &amp; WordPress developer"><img src="<?php echo COOKIE_MUNCH_IMAGES_URL; ?>/scott.ee.png" alt="scott logo"/></a>
		<p>Developed by <a href="http://scott.ee" title="Scott Evans - Web Designer and WordPress developer">Scott Evans</a>. If you find this plugin useful I'd be flattered to be Flattr'd:<br/><a class="FlattrButton" style="display:none; " rev="flattr;button:compact;" href="http://codecanyon.net/item/eu-cookie-muncher/2300176?ref=scottsweb"></a></p>
	</div>
	
</div>
<?php } ?>