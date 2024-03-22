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
	});

})(jQuery);