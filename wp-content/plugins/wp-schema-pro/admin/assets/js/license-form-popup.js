jQuery(document).ready( function() {

	jQuery( '#aiosrs-pro-license-form-btn' ).on( 'click', function(e) {
		e.preventDefault();

		jQuery('#aiosrs-pro-license-form').show();
		jQuery('body').addClass('aiosrs-pro-license-form-open');
	});

	jQuery('#aiosrs-pro-license-form-close-btn').on( 'click', function(e) {
		e.preventDefault();

		jQuery('#aiosrs-pro-license-form').hide();
		jQuery('body').removeClass('aiosrs-pro-license-form-open');
	});

	jQuery('#aiosrs-pro-license-form .aiosrs-pro-license-form-overlay').on( 'click', function(e) {
		e.preventDefault();

		jQuery('#aiosrs-pro-license-form').hide();
		jQuery('body').removeClass('aiosrs-pro-license-form-open');
	});

});