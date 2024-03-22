/* eslint-env jquery */
/**
 * File setup-wizard.js
 *
 * Handles Color Picker
 *
 * @package
 */

( function ( $ ) {
	jQuery( document ).ready( function () {
		$( document ).on(
			'click',
			'.aiosrs-pro-schema-temp-wrap',
			function ( e ) {
				e.preventDefault();

				$( '.aiosrs-pro-schema-temp-wrap' ).removeClass( 'selected' );
				$( '.aiosrs-pro-setup-actions' )
					.find( '.button-next' )
					.removeAttr( 'disabled' );
				$( this ).addClass( 'selected' );

				const type = $( this ).data( 'schema-type' ),
					title = $( this ).data( 'schema-title' );

				$( document ).find( '.bsf-aiosrs-schema-type' ).val( type );
				$( document ).find( '.bsf-aiosrs-schema-title' ).val( title );
			}
		);
	} );
} )( jQuery );
