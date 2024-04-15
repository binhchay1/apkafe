<?php
/**
 * Framework abstract.class file.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.

if ( ! class_exists( 'SP_EAP_Abstract' ) ) {
	/**
	 *
	 * Abstract Class
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	abstract class SP_EAP_Abstract {
		/**
		 * $abstract variable
		 *
		 * @var string
		 */
		public $abstract = '';
		/**
		 * $output_css variable
		 *
		 * @var string
		 */
		public $output_css = '';
		/**
		 * $typographies variable
		 *
		 * @var string
		 */
		public $typographies = array();

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			// Check for embed custom css styles.
			if ( ! empty( $this->args['output_css'] ) ) {
				add_action( 'wp_head', array( &$this, 'add_output_css' ), 100 );
			}

		}

		/**
		 * Add output css function.
		 */
		public function add_output_css() {

			$this->output_css = apply_filters( "eapro_{$this->unique}_output_css", $this->output_css, $this );

			if ( ! empty( $this->output_css ) ) {
				echo '<style type="text/css">' . esc_html( wp_strip_all_tags( $this->output_css ) ) . '</style>';
			}

		}

	}
}
