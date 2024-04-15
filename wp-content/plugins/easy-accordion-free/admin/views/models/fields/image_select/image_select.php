<?php
/**
 * Framework image_select field.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.

if ( ! class_exists( 'SP_EAP_Field_image_select' ) ) {
	/**
	 *
	 * Field: image_select
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_EAP_Field_image_select extends SP_EAP_Fields {

		/**
		 * Image select field constructor.
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
					'multiple' => false,
					'options'  => array(),
				)
			);

			$value = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->field_before();

			if ( ! empty( $args['options'] ) ) {

				echo '<div class="eapro-siblings eapro--image-group" data-multiple="' . esc_attr( $args['multiple'] ) . '">';

				$num = 1;

				foreach ( $args['options'] as $key => $option ) {

					$type     = ( $args['multiple'] ) ? 'checkbox' : 'radio';
					$extra    = ( $args['multiple'] ) ? '[]' : '';
					$active   = ( in_array( $key, $value ) ) ? ' eapro--active' : '';
					$checked  = ( in_array( $key, $value ) ) ? ' checked' : '';
					$pro_only = isset( $option['pro_only'] ) ? ' disabled' : '';

					echo '<div class="eapro--sibling ' . esc_attr( $pro_only ) . ' eapro--image' . esc_attr( $active ) . '">';
					if ( ! empty( $option['image'] ) ) {
						echo '<img src="' . esc_url( $option['image'] ) . '" alt="img-' . esc_attr( $num++ ) . '" />';
					} else {
						echo '<img src="' . esc_url( $option ) . '" alt="img-' . esc_attr( $num++ ) . '" />';
					}
					// phpcs:ignore
					echo '<input ' . esc_attr( $pro_only ) . ' type="' . esc_attr( $type ) . '" name="' . esc_attr( $this->field_name( $extra ) ) . '" value="' . esc_attr( $key ) . '"' . $this->field_attributes() . esc_attr( $checked ) . '/>';
					if ( ! empty( $option['option_name'] ) ) {
						echo '<p class="eap-image-name">' . esc_html( $option['option_name'] ) . '</p>';
					}

					echo '</div>';

				}

				echo '</div>';

			}

			echo '<div class="clear"></div>';
			// phpcs:ignore
			echo $this->field_after();

		}

	}
}
