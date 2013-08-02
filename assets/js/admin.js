var cm_picker = new Array();

(function($){
	
	$(document).ready( function() {
		
		$('.cm_colour_picker').each(function(index) {
						
			var input_id = '#'+$(this).parent().find('input[type="text"]').attr('id');
			var picker_id = '#'+$(this).attr('id');
			
			// activate farbtastic
			cm_picker[index] = $.farbtastic(picker_id, input_id);
			
			// set color on color wheel
			cm_picker[index].setColor($(input_id).val());
			
			// attach to button
			$(this).parent().find('.button').click( function(e) {
				$(picker_id).show();
				e.preventDefault();
			});			
			
			// keyboard helper		
			$(input_id).keyup( function() {
				var a = $(input_id).val(),
					b = a;
				
				a = a.replace(/[^a-fA-F0-9]/, '');
				
				if ( '#' + a !== b )
					$(input_id).val(a);
				if ( a.length === 3 || a.length === 6 )
					cm_picker[index].setColor('#'+ a);
					$(input_id).val('#'+ a);
			});
	 						
		});
		
		// hide colour pickers once used
		$(document).mousedown(function() {
			$('.cm_colour_picker').hide();
		});
		
		// add jquery change event to drop down to change the button message and hide the hide button
		// this is a little bit anyoying to change peoples text but the language does not to change - await feedback
		$('#cm_mode').bind('change', function() {
			if ($(this).val() == 'optout') {
				$('#cm_button_text').val('Decline Cookies');
				$('#cm_button_hide_row').show();
			}
			
			if ($(this).val() == 'optin') {
				$('#cm_button_text').val('Accept Cookies');
				$('#cm_button_hide_row').hide();
			}				
		});

		// on hide button click hide the button text input
		/*if($('#cm_button_hide').prop("checked", true)) {
			if ($('#cm_mode').val() == 'optout')) {
				$('#cm_button_text_row').hide();
			}
		} 

		$('#cm_button_hide').bind('change', function() {
			if ($('#cm_mode').val() == 'optout')) {
				if ($(this).is(':checked')) {
					$('#cm_button_text_row').hide();
				} else {
					$('#cm_button_text_row').show();
				}
			}
		});
		*/
				
	});

})(jQuery);