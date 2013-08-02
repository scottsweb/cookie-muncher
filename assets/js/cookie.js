jQuery(document).ready(function() {
	
	// only show if the cookie is not already set - fail safe for cached sites
	if (!cm_read_cookie('eu_cookies')) { 
	
		if (cookie_muncher_options.cookie_layout == 'top' || cookie_muncher_options.cookie_layout == 'bottom') {
			var cssprop = "height";
		} else {
			var cssprop = "opacity";
		}
		
		var animationArgs = {};
		animationArgs[cssprop] = "toggle";
		
		// show notification with button
		if (cookie_muncher_options.cookie_button_hide) {
	
			jQuery.noty({
				"text": cookie_muncher_options.cookie_message,
				"layout": cookie_muncher_options.cookie_layout,
				"animateOpen": animationArgs,
				"animateClose": animationArgs,
				"speed":500,
				"timeout": cookie_muncher_options.cookie_timeout,
				"closeButton": true,
				"closeOnSelfClick": true,
				"closeOnSelfOver": false,
				"modal": false,
				"onClose": function() {
					jQuery.get(cookie_muncher_options.cookie_close);
				},
				buttons: [
					{
						type: 'button', text: cookie_muncher_options.cookie_button_text, click: function($noty) {
							jQuery.noty.close();
							window.location.href = cookie_muncher_options.cookie_go;
						}
					}
				]
			});
		
		// show notification without button
		} else {
		
			jQuery.noty({
				"text": cookie_muncher_options.cookie_message,
				"layout": cookie_muncher_options.cookie_layout,
				"animateOpen": animationArgs,
				"animateClose": animationArgs,
				"speed":500,
				"timeout": cookie_muncher_options.cookie_timeout,
				"closeButton": true,
				"closeOnSelfClick": true,
				"closeOnSelfOver": false,
				"modal": false,
				"onClose": function() {
					jQuery.get(cookie_muncher_options.cookie_close);
				}
			});
			
		}
	}
});

function cm_read_cookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}