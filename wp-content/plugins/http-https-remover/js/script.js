var ajax_request_url = script_vars.ajax_url;
jQuery(document).ready(function($) {
	$("#remove_trans_anchor").on("click", function(e) {
		e.preventDefault();
		$.ajax({
			url: ajax_request_url,
			type: "POST",
			data: "action=jr_remove_trans",
			success: function(response) {
				jQuery('.http_custom_class .notice-dismiss').trigger('click');
			}
		});
	});
})