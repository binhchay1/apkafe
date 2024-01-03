<?php
/**
 * Premium License
 *
 * @since 1.0.0
 * @package Schema Pro
 */

?>

<div id="aiosrs-pro-license-form" style="display: none;">
	<div class="aiosrs-pro-license-form-overlay"></div>
	<div class="aiosrs-pro-license-form-inner">
		<button type="button" id="aiosrs-pro-license-form-close-btn">
			<span class="screen-reader-text"><?php esc_html_e( 'Close', 'wp-schema-pro' ); ?></span>
			<span class="dashicons dashicons-no-alt"></span>
		</button>

		<?php
			$bsf_product_id = bsf_extract_product_id( BSF_AIOSRS_PRO_DIR );
			$args           = array(
				'product_id'                       => $bsf_product_id,
				'button_text_activate'             => esc_html__( 'Activate License', 'wp-schema-pro' ),
				'button_text_deactivate'           => esc_html__( 'Deactivate License', 'wp-schema-pro' ),
				'license_form_title'               => '',
				'license_deactivate_status'        => esc_html__( 'Your license is not active!', 'wp-schema-pro' ),
				'license_activate_status'          => esc_html__( 'Your license is activated!', 'wp-schema-pro' ),
				'submit_button_class'              => 'bsf-product-license button-default',
				'form_class'                       => 'form-wrap bsf-license-register-' . esc_attr( $bsf_product_id ),
				'bsf_license_form_heading_class'   => 'bsf-license-heading',
				'bsf_license_active_class'         => 'success-message',
				'bsf_license_not_activate_message' => 'license-error',
				'size'                             => 'regular',
				'bsf_license_allow_email'          => false,
			);
			echo esc_html( bsf_license_activation_form( $args ) );
			?>

	</div>
</div>
