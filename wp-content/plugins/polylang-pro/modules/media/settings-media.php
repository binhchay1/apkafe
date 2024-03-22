<?php

/**
 * Settings class for media language and translation management
 * Advanced version
 *
 * @since 1.9
 */
class PLL_Settings_Media extends PLL_Settings_Module {
	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param object $polylang polylang object
	 */
	public function __construct( &$polylang ) {
		parent::__construct(
			$polylang,
			array(
				'module'        => 'media',
				'title'         => __( 'Media' ),
				'description'   => __( 'Activate languages and translations for media. Provides options for multilingual media management.', 'polylang-pro' ),
				'active_option' => 'media_support',
			)
		);
	}

	/**
	 * Displays the settings form
	 *
	 * @since 1.9
	 */
	protected function form() {
		printf(
			'<label for="duplicate-media"><input id="duplicate-media" name="media[duplicate]" type="checkbox" value="1" %s /> %s</label>',
			empty( $this->options['media']['duplicate'] ) ? '' : 'checked="checked"',
			esc_html__( 'Automatically duplicate media in all languages when uploading a new file.', 'polylang-pro' )
		);
	}

	/**
	 * Sanitizes the settings before saving
	 *
	 * @since 1.9
	 *
	 * @param array $options
	 */
	protected function update( $options ) {
		$newoptions['media']['duplicate'] = isset( $options['media']['duplicate'] ) ? 1 : 0;
		return $newoptions; // Take care to return only validated options
	}

	/**
	 * Get the row actions
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	protected function get_actions() {
		return empty( $this->options['media_support'] ) ? array( 'activate' ) : array( 'configure', 'deactivate' );
	}
}
