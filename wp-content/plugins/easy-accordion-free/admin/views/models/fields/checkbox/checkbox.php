<?php
/**
 * Framework checkbox field.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.

if ( ! class_exists( 'SP_EAP_Field_checkbox' ) ) {
	/**
	 *
	 * Field: checkbox
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_EAP_Field_checkbox extends SP_EAP_Fields {

		/**
		 * Checkbox field constructor.
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
					'inline'     => false,
					'query_args' => array(),
				)
			);

			$inline_class = ( $args['inline'] ) ? ' class="eapro--inline-list"' : '';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->field_before();

			if ( isset( $this->field['options'] ) ) {

				$value   = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );
				$options = $this->field['options'];
				$options = ( is_array( $options ) ) ? $options : array_filter( $this->field_data( $options, false, $args['query_args'] ) );

				if ( is_array( $options ) && ! empty( $options ) ) {

					echo '<ul' . wp_kses_post( $inline_class ) . '>';
					foreach ( $options as $option_key => $option_value ) {

						if ( is_array( $option_value ) && ! empty( $option_value ) ) {

							echo '<li>';
							echo '<ul>';
							echo '<li><strong>' . esc_attr( $option_key ) . '</strong></li>';
							foreach ( $option_value as $sub_key => $sub_value ) {
								$checked = ( in_array( $sub_key, $value, true ) ) ? ' checked' : '';
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo '<li><label><input type="checkbox" name="' . esc_attr( $this->field_name( '[]' ) ) . '" value="' . esc_attr( $sub_key ) . '"' . $this->field_attributes() . esc_attr( $checked ) . '/> ' . esc_attr( $sub_value ) . '</label></li>';
							}
							echo '</ul>';
							echo '</li>';

						} else {

							$checked = ( in_array( $option_key, $value, true ) ) ? ' checked' : '';
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo '<li><label><input type="checkbox" name="' . esc_attr( $this->field_name( '[]' ) ) . '" value="' . esc_attr( $option_key ) . '"' . $this->field_attributes() . $checked . '/> ' . esc_attr( $option_value ) . '</label></li>';

						}
					}
					echo '</ul>';

				} else {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo ( ! empty( $this->field['empty_message'] ) ) ? esc_attr( $this->field['empty_message'] ) : esc_html__( 'No data provided for this option type.', 'easy-accordion-free' );

				}
			} else {

				echo '<label class="eapro-checkbox">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<input type="hidden" name="' . esc_attr( $this->field_name() ) . '" value="' . $this->value . '" class="eapro--input"' . $this->field_attributes() . '/>';
				echo '<input type="checkbox" name="_pseudo" class="eapro--checkbox"' . esc_attr( checked( $this->value, 1, false ) ) . '/>';
				echo ( ! empty( $this->field['label'] ) ) ? ' ' . esc_attr( $this->field['label'] ) : '';
				echo '</label>';

			}
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->field_after();

		}

	}
}
