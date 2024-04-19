<?php
/**
 * Register plugin autoloader. Example namespace: Lasso\Models, Lasso\Pages, Lasso\Ajax,...
 *
 * @package Autoload
 *
 * @param string $class_name Name of the class to load.
 */

spl_autoload_register(
	function( $class_name ) {
		$namespace_prefix  = 'Lasso';
		$class_file_prefix = 'class-';

		// ? Only do autoload for our plugin files
		if ( strpos( $class_name, $namespace_prefix . '\\' ) === 0 ) {
			$class_file = str_replace( array( '\\', $namespace_prefix . DIRECTORY_SEPARATOR ), array( DIRECTORY_SEPARATOR, '' ), $class_name ) . '.php';
			$class_file = strtolower( $class_file );

			$temp              = explode( DIRECTORY_SEPARATOR, $class_file );
			$file_name         = end( $temp );
			$correct_file_name = $class_file_prefix . $file_name;
			$class_file        = str_replace( $file_name, $correct_file_name, $class_file );
			$class_file        = str_replace( '_', '-', $class_file );
			$class_file_path   = LASSO_PLUGIN_PATH . DIRECTORY_SEPARATOR . $class_file;

			if ( file_exists( $class_file_path ) ) {
				require_once $class_file_path;
			}
		}
	}
);
