<?php
/**
 * Framework validate file.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.

if ( ! function_exists( 'eapro_validate_email' ) ) {
	/**
	 * Email validate
	 *
	 * @param  string $value email.
	 * @return string
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_validate_email( $value ) {

		if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			return esc_html__( 'Please write a valid email address!', 'easy-accordion-free' );
		}

	}
}

if ( ! function_exists( 'eapro_validate_numeric' ) ) {
	/**
	 * Numeric validate
	 *
	 * @param  int $value int.
	 * @return int
	 */
	function eapro_validate_numeric( $value ) {

		if ( ! is_numeric( $value ) ) {
			return esc_html__( 'Please write a numeric data!', 'easy-accordion-free' );
		}

	}
}

if ( ! function_exists( 'eapro_validate_required' ) ) {
	/**
	 * Required validate
	 *
	 * @param  string $value string.
	 * @return string
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_validate_required( $value ) {

		if ( empty( $value ) ) {
			return esc_html__( 'Error! This field is required!', 'easy-accordion-free' );
		}

	}
}

if ( ! function_exists( 'eapro_validate_url' ) ) {
	/**
	 * URL validate
	 *
	 * @param  string $value value.
	 * @return string
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_validate_url( $value ) {

		if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return esc_html__( 'Please write a valid url!', 'easy-accordion-free' );
		}

	}
}

if ( ! function_exists( 'eapro_customize_validate_email' ) ) {
	/**
	 *
	 * Email validate for Customizer
	 *
	 * @param object $validity Email validity.
	 * @param string $value The Email.
	 * @param object $wp_customize Customize option.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_customize_validate_email( $validity, $value, $wp_customize ) {

		if ( ! sanitize_email( $value ) ) {
			$validity->add( 'required', esc_html__( 'Please write a valid email address!', 'easy-accordion-free' ) );
		}

		return $validity;

	}
}

if ( ! function_exists( 'eapro_customize_validate_numeric' ) ) {
	/**
	 *
	 * Numeric validate for Customizer
	 *
	 * @param object $validity Numeric validity.
	 * @param string $value The Numeric.
	 * @param object $wp_customize Customize option.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_customize_validate_numeric( $validity, $value, $wp_customize ) {

		if ( ! is_numeric( $value ) ) {
			$validity->add( 'required', esc_html__( 'Please write a numeric data!', 'easy-accordion-free' ) );
		}

		return $validity;

	}
}

if ( ! function_exists( 'eapro_customize_validate_required' ) ) {
	/**
	 *
	 * Required validate for Customizer
	 *
	 * @param object $validity Required validity.
	 * @param string $value The Required.
	 * @param object $wp_customize Customize option.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_customize_validate_required( $validity, $value, $wp_customize ) {

		if ( empty( $value ) ) {
			$validity->add( 'required', esc_html__( 'Error! This field is required!', 'easy-accordion-free' ) );
		}

		return $validity;

	}
}

if ( ! function_exists( 'eapro_customize_validate_url' ) ) {
	/**
	 *
	 * URL validate for Customizer
	 *
	 * @param object $validity URL validity.
	 * @param string $value The URL.
	 * @param object $wp_customize Customize option.
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function eapro_customize_validate_url( $validity, $value, $wp_customize ) {

		if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$validity->add( 'required', esc_html__( 'Please write a valid url!', 'easy-accordion-free' ) );
		}

		return $validity;

	}
}
