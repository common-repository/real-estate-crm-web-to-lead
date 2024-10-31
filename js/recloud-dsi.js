jQuery(document).ready(function() {

	if(jQuery('#dsidx-contact-form-submit').length) {
		jQuery('#dsidx-contact-form-submit').click(function() {
			jQuery('#dsidx-contact-form input[name=action]').val("recloud-connect-submit-dsi");
			var dsi_data = jQuery("#dsidx-contact-form input, #dsidx-contact-form textarea, #dsidx-contact-form select, #recloud_office_id").serialize();
			
			jQuery.post( recloud_dsi.ajaxurl, dsi_data, 
				function(data) {
					// DO NOTHING
				}, 
			"json");
			
			return false;
		});
	}
	
});