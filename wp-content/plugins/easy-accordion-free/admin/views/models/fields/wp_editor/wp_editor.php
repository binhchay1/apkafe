<?php
/**
 * Framework wp_editor field.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_EAP_Field_wp_editor' ) ) {
	/**
	 *
	 * Field: wp_editor
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_EAP_Field_wp_editor extends SP_EAP_Fields {

		/**
		 * Wp_Editor field constructor.
		 *
		 * @param array  $field The field type.
		 * @param string $value The values of the field.
		 * @param string $unique The unique ID for the field.
		 * @param string $where To where show the output CSS.
		 * @param string $parent The parent args.
		 */
		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {

			parent::__construct( $field, $value, $unique, $where, $parent );
		}

		/**
		 * Render field
		 *
		 * @return void
		 */
		public function render() {

			$args = wp_parse_args(
				$this->field,
				array(
					'tinymce'       => true,
					'quicktags'     => true,
					'media_buttons' => true,
					'height'        => '',
				)
			);

			$attributes = array(
				'rows'         => 10,
				'class'        => 'wp-editor-area',
				'autocomplete' => 'off',
			);

			$editor_height = ( ! empty( $args['height'] ) ) ? ' style="height:' . esc_attr( $args['height'] ) . ';"' : '';

			$editor_settings = array(
				'tinymce'       => $args['tinymce'],
				'quicktags'     => $args['quicktags'],
				'media_buttons' => $args['media_buttons'],
			);

			$allowed_tags = eapro_allowed_description_tags();
			$this->value  = wp_kses( $this->value, $allowed_tags );
			$this->value  = format_for_editor( $this->value );
     		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->field_before();
			echo ( eapro_wp_editor_api() ) ? '<div class="eapro-wp-editor" data-editor-settings="' . esc_attr( wp_json_encode( $editor_settings ) ) . '">' : '';
    		// phpcs:ignore
			echo '<textarea name="' . esc_attr( $this->field_name() ) . '"' . $this->field_attributes( $attributes ) . $editor_height . '>' . $this->value . '</textarea>';

			echo ( eapro_wp_editor_api() ) ? '</div>' : '';
      		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->field_after();

		}

		/**
		 * Field script enqueue
		 *
		 * @return void
		 */
		public function enqueue() {

			if ( eapro_wp_editor_api() && function_exists( 'wp_enqueue_editor' ) ) {

				wp_enqueue_editor();

				$this->setup_wp_editor_settings();

				add_action( 'print_default_editor_scripts', array( &$this, 'setup_wp_editor_media_buttons' ) );

			}

		}

		/**
		 * Setup wp editor media buttons.
		 */
		public function setup_wp_editor_media_buttons() {

			ob_start();
			echo '<div class="wp-media-buttons">';
			do_action( 'media_buttons' );
			echo '</div>';
			$media_buttons = ob_get_clean();

			echo '<script type="text/javascript">';
			echo 'var eapro_media_buttons = ' . wp_json_encode( $media_buttons ) . ';';
			echo '</script>';

		}

		/**
		 * Setup wp editor settings.
		 */
		public function setup_wp_editor_settings() {

			if ( eapro_wp_editor_api() && class_exists( '_WP_Editors' ) ) {

				$defaults = apply_filters(
					'eapro_wp_editor',
					array(
						'tinymce' => array(
							'wp_skip_init' => true,
						),
					)
				);

				$setup = _WP_Editors::parse_settings( 'eapro_wp_editor', $defaults );

				_WP_Editors::editor_settings( 'eapro_wp_editor', $setup );

			}

		}

	}
}

