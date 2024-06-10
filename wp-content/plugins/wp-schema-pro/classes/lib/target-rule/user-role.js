/* eslint-env jquery */
( function ( $ ) {
	const userRoleUpdateCloseButton = function ( wrapper ) {
		const rules = wrapper.find( '.bsf-user-role-condition' );
		let showClose = false;

		if ( rules.length > 1 ) {
			showClose = true;
		}

		rules.each( function () {
			if ( showClose ) {
				jQuery( this )
					.find( '.user_role-condition-delete' )
					.removeClass( 'bsf-hidden' );
			} else {
				jQuery( this )
					.find( '.user_role-condition-delete' )
					.addClass( 'bsf-hidden' );
			}
		} );
	};

	$( document ).ready( function () {
		jQuery( '.bsf-user-role-selector-wrapper' ).each( function () {
			userRoleUpdateCloseButton( jQuery( this ) );
		} );

		jQuery( '.bsf-user-role-selector-wrapper' ).on(
			'click',
			'.user_role-add-rule-wrap a',
			function ( e ) {
				e.preventDefault();
				e.stopPropagation();
				const $this = jQuery( this ),
					id = $this.attr( 'data-rule-id' ),
					newId = parseInt( id ) + 1,
					ruleWrap = $this
						.closest( '.bsf-user-role-selector-wrapper' )
						.find( '.user_role-builder-wrap' ),
					template = wp.template( 'bsf-user-role-condition' ),
					fieldWrap = $this.closest( '.bsf-user-role-wrapper' );

				ruleWrap.append( template( { id: newId } ) );

				$this.attr( 'data-rule-id', newId );

				userRoleUpdateCloseButton( fieldWrap );
			}
		);

		jQuery( '.bsf-user-role-selector-wrapper' ).on(
			'click',
			'.user_role-condition-delete',
			function () {
				const $this = jQuery( this ),
					ruleCondition = $this.closest( '.bsf-user-role-condition' ),
					fieldWrap = $this.closest( '.bsf-user-role-wrapper' );
				let cnt = 0;
				ruleCondition.remove();

				fieldWrap
					.find( '.bsf-user-role-condition' )
					.each( function ( i ) {
						const condition = jQuery( this ),
							oldRuleId = condition.attr( 'data-rule' ),
							selectLocation = condition.find(
								'.user_role-condition'
							),
							locationName = selectLocation.attr( 'name' );

						condition.attr( 'data-rule', i );

						selectLocation.attr(
							'name',
							locationName.replace(
								'[' + oldRuleId + ']',
								'[' + i + ']'
							)
						);

						condition
							.removeClass( 'bsf-user-role-' + oldRuleId )
							.addClass( 'bsf-user-role-' + i );

						cnt = i;
					} );

				fieldWrap
					.find( '.user_role-add-rule-wrap a' )
					.attr( 'data-rule-id', cnt );

				userRoleUpdateCloseButton( fieldWrap );
			}
		);
	} );
} )( jQuery );
