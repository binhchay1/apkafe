<?php

namespace LI\VCFJ;

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpers {

	public static function is_disabled( string $option ): bool {
		$options = get_option( 'vcfj_settings' );
		$option  = sprintf( 'vcfj_%s_disable', $option );

		return isset( $options[ $option ] ) && '1' === $options[ $option ];
	}

	public static function get_version( string $option ): string {
		$options     = get_option( 'vcfj_settings' );
		$option_name = sprintf( 'vcfj_%s_version', $option );

		return ! isset( $options[ $option_name ] ) || empty( $options[ $option_name ] ) ? 'latest' : $options[ $option_name ];
	}

	public static function get_cdn(): string {
		$options = get_option( 'vcfj_settings' );

		return ! isset( $options['vcfj_cdn'] ) || empty( $options['vcfj_cdn'] ) ? Plugin::DEFAULT_CDN : $options['vcfj_cdn'];
	}

}
