<?php
/*
Shortcode Name: Cookie Button [cookie_button enable_text="Cookies ON" disable_text="Cookies OFF"]
*/

/***************************************************************
* Function cookie_button_shortcode
* A shortcode for creating a cookie options button
***************************************************************/

add_shortcode('cookie_button', 'cookie_button_shortcode');

function cookie_button_shortcode($atts) {

    extract(shortcode_atts(array('disable_text' => __('Disable cookies on this website', 'cm'), 'enable_text' => __('Enable cookies on this website', 'cm')), $atts));
	
	// if the change has just been made
	if (isset($_GET['cookies'])) {
		
		$cookies = urldecode($_GET['cookies']);
		
		if ($cookies == 'on') {
			return decline_cookies_button($disable_text);
		} else {
			return accept_cookies_button($enable_text);
		}

	// if a cookie is already set
	} else if (isset($_COOKIE['eu_cookies'])) {
		
		if ($_COOKIE['eu_cookies'] == 'on') {
			return decline_cookies_button($disable_text);
		} else {
			return accept_cookies_button($enable_text);
		}
	
	// else base on plugin options
	} else {
		
		$options = get_option('cookie_muncher_options');
		
		if ($options['cm_mode'] == 'optin') {
			return accept_cookies_button($enable_text);
		} else {
			return decline_cookies_button($disable_text);
		}
	}
}

/***************************************************************
* Function accept_cookies_button & decline_cookies_button
* Helper functions to save repeating this
***************************************************************/

function accept_cookies_button($enable_text) {
    return '<a href="'.add_query_arg(array('cookies' => 'on'), cookie_current_url()).'" class="button enable-cookies-button">'.$enable_text.'</a>';
}

function decline_cookies_button($disable_text) {
    return '<a href="'.add_query_arg(array('cookies' => 'off'), cookie_current_url()).'" class="button disable-cookies-button">'.$disable_text.'</a>';
}
?>