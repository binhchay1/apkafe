<?php

namespace LI\VCFJ\Traits;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Initialise {

	private static $instance = null;

	public static function initialise() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
