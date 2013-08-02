<?php

/***************************************************************
* Function cookie_muncher_init
* Boot the plugin and respond accordingly 
***************************************************************/

add_filter( 'init', 'cookie_muncher_init');

function cookie_muncher_init() {    
		
	/** always setcookies with a domain **/
	if (COOKIE_DOMAIN == '') { $cookie_domain = str_replace(array('http://', 'www.', 'https://'), '', site_url()); } else { $cookie_domain == COOKIE_DOMAIN; }
	
	/** if this is the login page or register page then return **/
    if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) return;
	
	/** if get variable is set then set cookie on or off **/
	if (isset($_GET['cookies'])) {
		
		$cookies = urldecode($_GET['cookies']);
		
		// cookies on
		if ($cookies == 'on') {
			
			setcookie('eu_cookies', 'on', time()+3600*24*365, COOKIEPATH, $cookie_domain, false);
			return;
		
		// cookies off	
		} else {
		
			setcookie('eu_cookies', 'off', time()+3600*24*365, COOKIEPATH, $cookie_domain, false);
			add_action('template_redirect', 'cookie_muncher_start', -1);
			/** remove wordpress comment cookies - http://core.trac.wordpress.org/attachment/ticket/17976/17976.5.patch - hopefully making an appearance in 3.4 **/
			//remove_action('set_comment_cookies', 'wp_set_comment_cookies', 20); 
			return;
		}
		
	}
		
	/** if the cookie is set to ON then return **/
	if (isset($_COOKIE['eu_cookies']) && $_COOKIE['eu_cookies'] == 'on') return;

	/** if the cookies is set to OFF then strip cookies and return (already seen pop up message) **/
	if (isset($_COOKIE['eu_cookies']) && $_COOKIE['eu_cookies'] == 'off') {	
		add_action('template_redirect', 'cookie_muncher_start', -1);
		/** remove wordpress comment cookies - http://core.trac.wordpress.org/attachment/ticket/17976/17976.5.patch - hopefully making an appearance in 3.4 **/
		//remove_action('set_comment_cookies', 'wp_set_comment_cookies', 20); 
		return;
	}

	/** is the visitor is not in the EU then return **/
	if (!cookie_munch_eu()) return; 
	
	/** if we have made it this far then swtich our modes - first get options **/
	$options = get_option('cookie_muncher_options');

	/** build our message **/
	$message = '';
	$message .= $options['cm_message'];
	if ($options['cm_link_page']) {
		$page = get_post($options['cm_link_page']);
		$message .= ' <a href="'.get_permalink($page->ID).'" title="'.$page->post_title.'">'.$options['cm_link_text'].'</a>';
	}
			
	// switch the mode new for 1.2 
	switch($options['cm_mode']) {
	
		case "optout":

			/** javascript **/
			wp_enqueue_script('cookie-noty',COOKIE_MUNCH_JS_URL.'/noty.js', array('jquery'), filemtime(COOKIE_MUNCH_PATH.'/assets/js/noty.js'), false);
			wp_enqueue_script('cookie-muncher',COOKIE_MUNCH_JS_URL.'/cookie.js', array('jquery', 'cookie-noty'), filemtime(COOKIE_MUNCH_PATH.'/assets/js/cookie.js'), false);
			wp_localize_script('cookie-muncher','cookie_muncher_options', array(
				'cookie_message' => $message,
				'cookie_layout' => $options['cm_position'],
				'cookie_timeout' => ($options['cm_timeout'] == 0 ? false : $options['cm_timeout'] ),
				'cookie_button_hide' => (isset($options['cm_button_hide']) ? false : 1 ),
				'cookie_button_text' => ($options['cm_button_text'] == '' ? __("Decline Cookies", 'cm') : $options['cm_button_text'] ),
				'cookie_go' => add_query_arg(array('cookies' => 'off'), cookie_current_url()),
				'cookie_close' => add_query_arg(array('cookies' => 'on'), cookie_current_url()),
			));
		
			/** css **/
			// if style is duplicated in the theme load the them version, else load the default plugin style and custom colours
			if (file_exists(STYLESHEETPATH.'/cookie_muncher.css')) {
				wp_enqueue_style('cmcss',get_stylesheet_directory_uri().'/cookie_muncher.css', array(), filemtime(STYLESHEETPATH.'/cookie_muncher.css'),'all');
			} else {
				wp_enqueue_style('cmcss',COOKIE_MUNCH_CSS_URL.'/cookie_muncher.css', array(), filemtime(COOKIE_MUNCH_PATH.'/assets/css/cookie_muncher.css'),'all');
				add_action('wp_head', 'cookie_munch_custom_colours');
			}
					
		break;
		
		// opt in / default mode from first version of plugin
		default:
				
			/** javascript **/
			wp_enqueue_script('cookie-noty',COOKIE_MUNCH_JS_URL.'/noty.js', array('jquery'), filemtime(COOKIE_MUNCH_PATH.'/assets/js/noty.js'), false);
			wp_enqueue_script('cookie-muncher',COOKIE_MUNCH_JS_URL.'/cookie.js', array('jquery', 'cookie-noty'), filemtime(COOKIE_MUNCH_PATH.'/assets/js/cookie.js'), false);
			wp_localize_script('cookie-muncher','cookie_muncher_options', array(
				'cookie_message' => $message,
				'cookie_layout' => $options['cm_position'],
				'cookie_timeout' => ($options['cm_timeout'] == 0 ? false : $options['cm_timeout'] ),
				'cookie_button_hide' => 1,
				'cookie_button_text' => ($options['cm_button_text'] == '' ? __("Accept Cookies", 'cm') : $options['cm_button_text'] ),
				'cookie_go' => add_query_arg(array('cookies' => 'on'), cookie_current_url()),
				'cookie_close' => add_query_arg(array('cookies' => 'off'), cookie_current_url()),
			));
		
			/** css **/
			// if style is duplicated in the theme load the them version, else load the default plugin style and custom colours
			if (file_exists(STYLESHEETPATH.'/cookie_muncher.css')) {
				wp_enqueue_style('cmcss',get_stylesheet_directory_uri().'/cookie_muncher.css', array(), filemtime(STYLESHEETPATH.'/cookie_muncher.css'),'all');
			} else {
				wp_enqueue_style('cmcss',COOKIE_MUNCH_CSS_URL.'/cookie_muncher.css', array(), filemtime(COOKIE_MUNCH_PATH.'/assets/css/cookie_muncher.css'),'all');
				add_action('wp_head', 'cookie_munch_custom_colours');
			}
			
			/** attach the cookie muncher **/
			add_action('template_redirect', 'cookie_muncher_start', -1);
			
			/** remove wordpress comment cookies - http://core.trac.wordpress.org/attachment/ticket/17976/17976.5.patch - hopefully making an appearance in 3.4 **/
			//remove_action('set_comment_cookies', 'wp_set_comment_cookies', 20); 
		
		break;

	}
}

/***************************************************************
* Function cookie_munch_custom_colours
* Output our custom design options to the theme header
***************************************************************/

function cookie_munch_custom_colours() {
	
	$options = get_option('cookie_muncher_options');
	?>
	<!-- eu cookie muncher custom styles -->
	<style type="text/css">
	.noty_bar.noty_theme_default { background: <?php echo $options['cm_background_colour'] ?>; color: <?php echo $options['cm_text_colour'] ?>; } 
	.noty_bar.noty_theme_default a, .noty_bar.noty_theme_default a:visited { color: <?php echo $options['cm_link_colour'] ?>;}
	.noty_bar.noty_theme_default a:hover, .noty_bar.noty_theme_default a:active { color: <?php echo $options['cm_link_hover_colour'] ?>;}
	</style>
	<?php
}

/***************************************************************
* Function cookie_munch_eu
* Check if the current user is in the EU (returns true if in EU)
***************************************************************/

function cookie_munch_eu() {
	
	// if settings not enabled then return true
	$options = get_option('cookie_muncher_options');
	if (!isset($options['cm_euonly'])) return true;
	
	$eu_countries = array(
		"AT" => "Austria",
		"BE" => "Belgium",
		"BG" => "Bulgaria",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"DK" => "Denmark",
		"EE" => "Estonia",
		"FI" => "Finland",
		"FR" => "France",
		"DE" => "Germany",
		"GR" => "Greece",
		"HU" => "Hungary",
		"IE" => "Ireland",
		"IT" => "Italy",
		"LV" => "Latvia",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MT" => "Malta",
		"NL" => "Netherlands",
		"PL" => "Poland",
		"PT" => "Portugal",
		"RO" => "Romania",
		"SK" => "Slovakia (Slovak Republic)",
		"SI" => "Slovenia",
		"ES" => "Spain",
		"SE" => "Sweden",
		"GB" => "United Kingdom",
		"RD" => "Local IP" // added for testing purposes
	);

	$url = 'http://freegeoip.net/json/'.$_SERVER['REMOTE_ADDR'];
	
	$response = wp_remote_get($url, array(
		'timeout' => 10
	));

	if (is_wp_error($response)) {
		
		// error - return true to be safe
		return true;
	
	} else {
		
		$json = json_decode($response['body']);
				
		if (isset($json->country_code)) {
			if (isset($eu_countries[$json->country_code])) {
				// the country code appears to be in europe
				return true;
			} else {
				// not in europe
				return false;
			}
		} else {
			// we do not recognise the country code
			return true;
		}
	}
}

/***************************************************************
* Class cookie_munch
* Use regular expressions to strip out known 
***************************************************************/

function cookie_muncher_start() {
	if (!is_feed()) {
		ob_start('cookie_muncher_finish');
	}
}

function cookie_muncher_finish($html) {
	return new cookie_muncher($html);
}

class cookie_muncher {
	
	/* variables */
	protected $html;
	
	public function __construct($html) {
		if (!empty($html)) {
			$this->parse($html);
		}
	}
	
	public function __toString() {
		return $this->html;
	}
	
	protected function info($count) {
		if (isset($_GET['action'])) return false; // do not interfere with ajax requests
		if (defined('DOING_AJAX') && DOING_AJAX) return false;
		return "\n" . '<!-- '. sprintf(_n('%d Cookie Munched!', '%d Cookies Munched!', $count), $count) .' -->';
	}
		
	public function parse($html) {
		
		$patterns = array();
		$count = 0;
				
		// Google Analytics
		$patterns[] = '/(?i)<script[^<]*google-analytics\.com\/ga.js[^<]*<\/script>/i';
		$patterns[] = '/(?i)<script[^<]*(urchin)[^<]*<\/script>/i';
		$patterns[] = '/(?i)<script[^<]*pageTracker[^<]*<\/script>/i';
		
		// WordPress Stats
		$patterns[] = '/(?i)<script[^<]*stats\.wordpress\.com[^<]*<\/script>/i';
		
		// Twitter
		$patterns[] = '/(?i)<script[^<]*!function\(d,s,id\)[^<]*platform\.twitter\.com[^<]*<\/script>/i';
		$patterns[] = '/(?i)<script[^<]*widgets\.twimg\.com[^<]*<\/script>/i';
		
		// Tweetmeme
		$patterns[] = '/(?i)<script[^<]*tweetmeme\.com\/i\/scripts\/button\.js[^<]*<\/script>/i';
		$patterns[] = '/(?i)<script[^<]*tweetmeme_source[^<]*<\/script>/i';
		
		// Facebook
		$patterns[] = '/(?i)<script[^<]*connect\.facebook\.net[^<]*<\/script>/i';
		$patterns[] = '/(?i)<iframe[^<]*facebook\.com\/plugins\/like\.php[^<]*<\/iframe>/i';
		$patterns[] = '/(?i)<iframe[^<]*facebook\.com\/plugins\/likebox\.php[^<]*<\/iframe>/i';
		
		// Google+
		$patterns[] = '/(?i)<script[^<]*apis\.google\.com\/js\/plusone\.js[^<]*<\/script>/i';
		
		// StumbleUpon
		$patterns[] = '/(?i)<script[^<]*stumbleupon\.com\/hostedbadge\.php[^<]*<\/script>/i';
		
		// Pinterest
		$patterns[] = '/(?i)<script[^<]*assets\.pinterest\.com\/js\/pinit\.js[^<]*<\/script>/i';
		
		// Digg
		$patterns[] = '/(?i)<script[^<]*widgets\.digg\.com\/buttons\.js[^<]*<\/script>/i';
		
		// ShareThis - http://sharethis.com/
		$patterns[] = '/(?i)<script[^<]*w\.sharethis\.com\/button\/buttons\.js[^<]*<\/script>/i';
		
		// AddThis - http://www.addthis.com/
		$patterns[] = '/(?i)<script[^<]*addthis\.com\/js\/[^<]*<\/script>/i';
		
		// CompeteXL - http://www.ghostery.com/apps/competexl
		$patterns[] = '/(?i)<script[^<]*compete\.com\/bootstrap[^<]*<\/script>/i';
		
		// Parsely - http://www.ghostery.com/apps/parse.ly
		$patterns[] = '/(?i)<script[^<]*static\.parsely\.com\/p.js[^<]*<\/script>/i';
		
		// Kissmetrics - http://www.ghostery.com/apps/kissmetrics
		$patterns[] = '/(?i)<script[^<]*i\.kissmetrics\.com[^<]*<\/script>/i';
		
		// Peerius - http://www.ghostery.com/apps/peerius
		$patterns[] = '/(?i)<script[^<]*peerius\.com\/tracker[^<]*<\/script>/i';
		
		// Clicky - http://www.ghostery.com/apps/clicky
		$patterns[] = '/(?i)<script[^<]*static\.getclicky\.com\/js[^<]*<\/script>/i';
		$patterns[] = '/(?i)<noscript>.*in\.getclicky\.com.*<\/noscript>/i';
		//$patterns[] = '/(?i)<img[^<]*static\.getclicky\.com[^>]*>/i';
		
		// chart.dk
		$patterns[] = '/(?i)<script[^<]*chart\.dk\/js[^<]*<\/script>/i';
		$patterns[] = '/(?i)<script[^<]*track_visitor[^<]*<\/script>/i';
		$patterns[] = '/(?i)<noscript>(.|\n)*chart\.dk\/ref\.asp(.|\n)*<\/noscript>/i';		
		
		// dpSocialShare - http://codecanyon.net/item/wordpress-social-share/2025994
		$patterns[] = '/(?i)<script[^<]*jquery\.dpSocialShare[^<]*<\/script>/i';
		
		// Woopra - http://www.ghostery.com/apps/woopra
		$patterns[] = '/(?i)<script[^<]*static\.woopra\.com\/js\/woopra\.js[^<]*<\/script>/i';
		
		// Webtrends - http://www.ghostery.com/apps/webtrends
		$patterns[] = '/(?i)<script[^>]*webtrends[^>]*><\/script>/i';
		$patterns[] = '/(?i)<noscript>(.|\n)*statse\.webtrendslive\.com(.|\n)*<\/noscript>/i';
		
		// Advertising
		$options = get_option('cookie_muncher_options');
		if ($options['cm_adverts']) {
			
			// Doubleclick / Google Adsense
			$patterns[] = '/(?i)(?i)<script[^<]*ad\.doubleclick\.net[^<]*<\/script>/i';
			$patterns[] = '/(?i)<script[^<]*googleadservices\.com[^<]*<\/script>/i';
			$patterns[] = '/(?i)<iframe[^<]*fls\.doubleclick.\net[^<]*<\/iframe>/i';
			
			// BuySellAds
			$patterns[] = '/(?i)<script[^<]*buysellads\.com\/ac\/bsa\.js[^<]*<\/script>/i';
			
			// SkimLinks
			$patterns[] = '/(?i)<script[^<]*skimlinks_domain[^<]*<\/script>/i';
			$patterns[] = '/(?i)<script[^<]*skimresources.com\/js\/[^<]*<\/script>/i';
			
			// Adbrite
			$patterns[] = '/(?i)<script[^<]*AdBrite_URL_Color[^<]*<\/script>/i';
			$patterns[] = '/(?i)<script[^<]*AdBrite_Referrer[^<]*<\/script>/i';

		}
		
		$this->html = preg_replace($patterns, '', $html, -1, $count);
		$this->html .= $this->info($count);
	}
	
}

/***************************************************************
* Function cookie_current_url
* Computes the *full* URL of the current page (protocol, server, path, query parameters, etc) 
***************************************************************/

function cookie_current_url() {
    $s = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
    $protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
    $port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (":".$_SERVER['SERVER_PORT']);
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
}

/***************************************************************
* Function cookies_on
* Are cookies on? 
***************************************************************/

function cookies_on() {
	
	if (isset($_GET['cookies'])) {
		$cookies = urldecode($_GET['cookies']);
		if ($cookies == "off") return false;
		if ($cookies == "on") return true;
	}
	
	if (isset($_COOKIE['eu_cookies']) && $_COOKIE['eu_cookies'] == 'on') return true;
	
	$options = get_option('cookie_muncher_options');
	if ($options['cm_mode'] == "optout") return true;

	return false;
}

/***************************************************************
* Function cookies_off
* Are cookies off?
***************************************************************/

function cookies_off() {

	if (isset($_GET['cookies'])) {
		$cookies = urldecode($_GET['cookies']);
		if ($cookies == "on") return false;
		if ($cookies == "off") return true;
	}
	
	if (isset($_COOKIE['eu_cookies']) && $_COOKIE['eu_cookies'] == 'off') return true;

	$options = get_option('cookie_muncher_options');
	if ($options['cm_mode'] == "optin") return true;

	return false;
}
?>