jQuery( document ).ready( function( $ ) {
	// Ajax actions when changing the product language
	$( '.post_lang_choice' ).change( function() {
		var attributes = new Array();

		// Get the attributes name and index
		$( ':input[name*="attribute_names"]' ).each( function() {
			name = $( this ).attr( 'name' );
			n = name.substring( 16, name.length - 1 );
			attributes[n] = $( this ).val();
		});

		if ( attributes.length ) {
			var data = {
				action: 'product_lang_choice',
				lang: $( this ).val(),
				post_id: $( '#post_ID' ).val(),
				attributes: attributes,
				_pll_nonce: $( '#_pll_nonce' ).val()
			}

			$.post( ajaxurl, data , function( response ) {
				var res = wpAjax.parseAjaxResponse( response, 'ajax-response' );
				$.each( res.responses, function() {
					switch ( this.what ) {
						case 'attributes':
							// Replace only options to avoid loosing the bind with the select2
							$.each( this.supplemental, function( i, value ) {
								$( ':input[name="attribute_values[' + i.substring( 6 ) + '][]"]' ).html( value ).change();
							});
						break;
					}
				});
			});
		}
	});
});
