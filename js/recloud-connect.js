jQuery(document).ready(function() {


	jQuery('.recloud_after_form_wrapper').hide();

	jQuery('.recloud-connect-submit').click(function() {

		var recloud_connect_form = jQuery(this).parent();
		var recloud_after_form = jQuery(this).parent().parent().children('.recloud_after_form_wrapper');

		jQuery.post( recloud_connect.ajaxurl, recloud_connect_form.serialize(), 
			function(data) {
				// DO NOTHING
			}, 
		"json");
			
		recloud_connect_form.fadeOut('slow', function() {
			recloud_after_form.fadeIn('slow');
		});
		
		return false;
	});

});