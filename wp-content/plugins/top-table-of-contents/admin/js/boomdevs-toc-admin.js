(function ($) {
	'use strict';

	$(document).ready(function () {

		// Load data for pre-made layout skin
		$('.premade_layouts').find('.csf--sibling').unbind( 'click' );

		$('.premade_layouts .csf--image').click(function (event) {
			event.preventDefault();

			if (confirm(boomdevs_toc_messages.skin_change_confirmation_alert) === true) {
				var skin = $(this).find('input').val();

				$.ajax({
					type: 'POST',
					url: bd_toc_content.ajaxurl,
					data: {
						'action': bd_toc_content.action,
						'nonce': bd_toc_content.nonce,
						'skin': skin,
					},
					success: function (data) {
						if (data['status'] === 'success') {
							alert(boomdevs_toc_messages.skin_change_alert);
							window.location.reload();
						} else {
							alert(data['message']);
						}
					}
				});
			}
		});

		// Attach click event handler to the install Plugin
		$('.toc-custom-landing-install-btn').on('click', function(e) {
			e.preventDefault();
	
			let $button = $(this);
			$button.prop('disabled', true);
			$button.find('.toc-custom-landing-install-btn-txt').text('Installing...');
			let targetUrl = $button.data('target-url');
	
			$.ajax({
				url: Boomdevs_Toc_custom_plugin_install_obj.ajax_url,
				type: 'POST',
				data: {
					action: 'Boomdevs_Toc_custom_plugin_install',
					security: Boomdevs_Toc_custom_plugin_install_obj.security
				},
				success: function(response) {
					$button.prop('disabled', false);
					$button.find('.toc-custom-landing-install-btn-txt').text('Plugin Activated');
					// Redirect to the specified URL after a short delay
					setTimeout(function() {
						window.location.href = targetUrl;
					}, 1000);
				},
				error: function() {
					alert('An error occurred during the installation process.');
					$button.prop('disabled', false);
					$button.find('.toc-custom-landing-install-btn-txt').text('Install Ai Alt Text - Free');
				}
			});
		});



	});

})(jQuery);