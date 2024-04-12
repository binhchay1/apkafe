<?php

namespace LI\VCFJ;

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Enqueue {

	use Traits\Initialise;

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_core_version' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_migrate_version' ) );
	}

	public function register_core_version(): void {
		wp_deregister_script( 'jquery' );

		if ( Helpers::is_disabled( 'core' ) ) {
			return;
		}

		$version = Helpers::get_version( 'core' );

		if ( 'git-build' === $version ) {
			wp_register_script( 'jquery', 'https://code.jquery.com/jquery-git.min.js', false, $version, false );
			return;
		}

		if ( 'latest' === $version ) {
			$version = Plugin::DEFAULT_CORE;
		}

		$cdn = Helpers::get_cdn();

		if ( ! array_key_exists( $cdn, Mappings::$core ) ) {
			$cdn = Plugin::DEFAULT_CDN;
		}

		if ( 'cdnjs' === $cdn ) {
			switch ( $version ) {
				case '1.3':
					$version = '1.3.0';
					break;
			}
		} elseif ( 'google' === $cdn ) {
			switch ( $version ) {
				case '1.7':
					$version = '1.7.0';
					break;
				case '1.5':
					$version = '1.5.0';
					break;
				case '1.3':
					$version = '1.3.0';
					break;
			}
		}

		$has_version = in_array( $version, Mappings::$core[ $cdn ], true );

		if ( ! $has_version || 'jquery' === $cdn ) {
			$url = sprintf( 'https://code.jquery.com/jquery-%s.min.js', $version );
		} elseif ( 'cdnjs' === $cdn ) {
			$url = sprintf( 'https://cdnjs.cloudflare.com/ajax/libs/jquery/%s/jquery.min.js', $version );
		} elseif ( 'google' === $cdn ) {
			$url = sprintf( 'https://ajax.googleapis.com/ajax/libs/jquery/%s/jquery.min.js', $version );
		} elseif ( 'jsdelivr' === $cdn ) {
			$url = sprintf( 'https://cdn.jsdelivr.net/npm/jquery@%s/dist/jquery.min.js', $version );
		}

		wp_register_script( 'jquery', $url, false, $version, false );
	}

	public function register_migrate_version(): void {
		wp_deregister_script( 'jquery-migrate' );

		if ( Helpers::is_disabled( 'migrate' ) ) {
			return;
		}

		$version = Helpers::get_version( 'migrate' );

		if ( 'git-build' === $version ) {
			wp_enqueue_script( 'jquery-migrate', 'https://code.jquery.com/jquery-migrate-git.min.js', array( 'jquery' ), $version, false );
			return;
		}

		if ( 'latest' === $version ) {
			$version = Plugin::DEFAULT_MIGRATE;
		}

		$cdn = Helpers::get_cdn();

		if ( ! array_key_exists( $cdn, Mappings::$migrate ) ) {
			$cdn = Plugin::DEFAULT_CDN;
		}

		$has_version = in_array( $version, Mappings::$migrate[ $cdn ], true );

		if ( ! $has_version || 'jquery' === $cdn ) {
			$url = sprintf( 'https://code.jquery.com/jquery-migrate-%s.min.js', $version );
		} elseif ( 'cdnjs' === $cdn ) {
			$url = sprintf( 'https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/%s/jquery-migrate.min.js', $version );
		} elseif ( 'jsdelivr' === $cdn ) {
			$url = sprintf( 'https://cdn.jsdelivr.net/npm/jquery-migrate@%s/dist/jquery-migrate.min.js', $version );
		}

		wp_enqueue_script( 'jquery-migrate', $url, array( 'jquery' ), $version, false );
	}

}

Enqueue::initialise();
