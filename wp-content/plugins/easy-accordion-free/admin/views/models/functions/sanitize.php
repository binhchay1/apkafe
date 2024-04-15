<?php
/**
 * Framework sanitize file.
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

if ( ! function_exists( 'eapro_sanitize_replace_a_to_b' ) ) {
	/**
	 *
	 * Sanitize
	 * Replace letter a to letter b
	 *
	 * @param  string $value string.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_sanitize_replace_a_to_b( $value ) {

		return str_replace( 'a', 'b', $value );
	}
}

if ( ! function_exists( 'eapro_sanitize_title' ) ) {
	/**
	 *
	 * Sanitize title
	 *
	 * @param  string $value string.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_sanitize_title( $value ) {

		return sanitize_title( $value );
	}
}

if ( ! function_exists( 'eapro_allowed_title_tags' ) ) {
	/**
	 *
	 * Sanitize allowed html tags.
	 */
	function eapro_allowed_title_tags() {

		$allowed_tags = array(
			'b'      => array(),
			'strong' => array(),
			'i'      => array(),
			'u'      => array(),
		);

		return apply_filters( 'sp_easy_accordion_title_allowed_tags', $allowed_tags );
	}
}

if ( ! function_exists( 'eapro_allowed_description_tags' ) ) {
	/**
	 *
	 * Sanitize allowed html tags.
	 */
	function eapro_allowed_description_tags() {

		$allowed_tags           = wp_kses_allowed_html( 'post' );
		$allowed_tags['iframe'] = array(
			'src'             => array(),
			'height'          => array(),
			'width'           => array(),
			'frameborder'     => array(),
			'allowfullscreen' => array(),
			'title'           => array(),
			'alt'             => array(),
			'class'           => array(),
			'id'              => array(),
		);
		$allowed_tags['style']  = array(
			'type'  => array(),
			'media' => array(),
		);

		// Add attributes for the 'audio' tag.
		$allowed_tags['audio'] = array(
			'controls' => true,
			'src'      => array(),
			'autoplay' => array(),
			'loop'     => array(),
			'preload'  => array(),
			'muted'    => array(),
		);

		// Add attributes for the 'source' tag.
		$allowed_tags['source'] = array(
			'src'    => array(),
			'type'   => array(),
			'media'  => array(),
			'sizes'  => array(),
			'srcset' => array(),
		);
		// Add attributes for the 'video' tag.
		$allowed_tags['video'] = array(
			'src'      => array(),
			'autoplay' => array(),
			'controls' => array(),
			'width'    => array(),
			'height'   => array(),
			'loop'     => array(),
			'preload'  => array(),
			'poster'   => array(),
			'muted'    => array(),
		);

		return apply_filters( 'sp_easy_accordion_desc_allowed_tags', $allowed_tags );
	}
}

if ( ! function_exists( 'eapro_sanitize_accordion_title_content' ) ) {
	/**
	 *
	 * Sanitize the accordion title and content in group field only.
	 *
	 * @param  array $value array.
	 */
	function eapro_sanitize_accordion_title_content( $value ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$sanitized_data                 = array();
		$eapro_allowed_title_tags       = eapro_allowed_title_tags();
		$eapro_allowed_description_tags = eapro_allowed_description_tags();

		if ( is_array( $value ) ) {
			// $value is an multi-dimensional array of a group field.
			foreach ( $value as $key => $fields_value ) {

				if ( ! empty( $fields_value ) && is_array( $fields_value ) ) {

					$data = array(); // This variable stores single accordion group data.
					foreach ( $fields_value as $k => $field_value ) {

						if ( ! empty( $field_value ) && 'accordion_content_title' === $k ) {
							// Sanitize Accordion Item Title.
							$field_value = wp_kses( $field_value, $eapro_allowed_title_tags );
						} elseif ( ! empty( $field_value ) && 'accordion_content_description' === $k ) {
							// Sanitize Accordion Item Content.
							$field_value = wp_kses( $field_value, $eapro_allowed_description_tags );
						} else {
							$field_value = wp_kses_post( $field_value );
						}

						$data[ sanitize_key( $k ) ] = $field_value;
					}
				}
				$sanitized_data[ sanitize_key( $key ) ] = $data;
			}
		}
		return $sanitized_data;
	}
}


if ( ! function_exists( 'eapro_sanitize_number_array_field' ) ) {
	/**
	 *
	 * Sanitize number array
	 *
	 * @param  mixed $array value.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_sanitize_number_array_field( $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return array();
		}

		$new_array = array();
		foreach ( $array as $key => $value ) {
			$sanitize_key = sanitize_key( $key );
			if ( 'unit' === $key || 'units' === $key ) {
				$new_array[ $sanitize_key ] = wp_filter_nohtml_kses( $value );
			} else {
				$new_array[ $sanitize_key ] = intval( $value );
			}
		}
		return $new_array;
	}
}

if ( ! function_exists( 'eapro_sanitize_number_field' ) ) {
	/**
	 *
	 * Sanitize number
	 *
	 * @param  mixed $value value.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_sanitize_number_field( $value ) {
		if ( empty( $value ) ) {
			return 0;
		} else {
			return intval( $value );
		}
	}
}

if ( ! function_exists( 'eapro_sanitize_border_field' ) ) {
	/**
	 *
	 * Sanitize border field
	 *
	 * @param  mixed $array value.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_sanitize_border_field( $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return array();
		}

		$new_array = array();
		foreach ( $array as $key => $value ) {
			$sanitize_key = sanitize_key( $key );
			if ( 'style' == $key || strpos( $key, 'color' ) !== false ) {
				$new_array[ $sanitize_key ] = sanitize_text_field( $value );
			} elseif ( ! empty( $value ) ) {
				$new_array[ $sanitize_key ] = intval( $value );
			}
		}
		return $new_array;
	}
}

if ( ! function_exists( 'eapro_sanitize_color_group_field' ) ) {
	/**
	 *
	 * Sanitize color group field
	 *
	 * @param  mixed $array value.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_sanitize_color_group_field( $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return array();
		}

		$new_array = array();
		foreach ( $array as $key => $value ) {
			$sanitize_key               = sanitize_key( $key );
			$new_array[ $sanitize_key ] = sanitize_text_field( $value );
		}
		return $new_array;
	}
}
