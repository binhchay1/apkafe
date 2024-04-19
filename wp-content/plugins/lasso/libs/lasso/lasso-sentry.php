<?php
/**
 * Declare sentry
 *
 * @package lasso sentry
 */

if ( ! function_exists( 'is_lasso_function_enabled' ) ) {
	/**
	 * Check whether a funcion is disabled or not
	 * some functions are disabled by hosting/server
	 * so we need to check whether a function is disabled or not
	 *
	 * @param string $function_name Function name.
	 */
	function is_lasso_function_enabled( $function_name ) {
		$disabled = explode( ',', ini_get( 'disable_functions' ) );
		return ! in_array( $function_name, $disabled, true ) && function_exists( $function_name );
	}
}

function is_lasso_page() {
	global $pagenow;

	$is_edit_php = 'edit.php' === $pagenow;
	$post_type = $_GET['post_type'] ?? '';
	$post_action = $_POST['action'] ?? '';
	$is_lasso_ajax = strpos($post_action, 'lasso_') === 0 ? true : false;
	$is_lasso_page = ( LASSO_POST_TYPE === $post_type && $is_edit_php ) || $is_lasso_ajax;

	$is_post_php = 'post.php' === $pagenow;
	$action = $_GET['action'] ?? '';
	$is_post_edit_page = 'edit' === $action && $is_post_php;

	$result = $is_lasso_page || $is_post_edit_page;

	return $result;
}

if ( is_lasso_function_enabled( 'php_uname' ) && is_lasso_page() ) {
	$lasso_php_required = 7.2;
	try {
		// ? loading Sentry SDK
		if ( (float) PHP_VERSION >= $lasso_php_required ) {
			require_once LASSO_PLUGIN_PATH . '/vendor-prefix/vendor/autoload.php';

			\LassoVendor\Sentry\init(
				array(
					'dsn'                => SENTRY_DNS,
					'release'            => LASSO_VERSION,
					'before_send'        => function ( \LassoVendor\Sentry\Event $event ) {
						// @codingStandardsIgnoreStart
						// ? just send error event to sentry if the file in our plugin raises an error.
						$exceptions = $event->getExceptions();
						$stacktrace = $exceptions[0]->getStacktrace(); // Sentry v3
						$error_message = $exceptions[0]->getValue() ?? ''; // Sentry v3
						// $stacktrace = $exceptions[0]['stacktrace']; // Sentry v2
						// $error_message = $exceptions[0]['value'] ?? ''; // Sentry v2
						$frames     = $stacktrace->getFrames();
						$last_frame = end( $frames );
						// @codingStandardsIgnoreEnd

						if ( '' !== $error_message &&
							(
								strpos( $error_message, 'Warning: is_readable(): open_basedir restriction in effect. File(/proc/stat)' ) !== false
								|| strpos( $error_message, 'Warning: preg_match(): Allocation of JIT memory failed, PCRE JIT will be disabled.' ) !== false
								|| strpos( $error_message, 'Error: Out of memory' ) !== false
							)
						) {
							return null;
						}

						if ( strpos( $last_frame->getFile(), LASSO_PLUGIN_PATH ) !== false ) {
							return $event;
						}

						return null;
					},
					'traces_sample_rate' => 1.0,
				)
			);

			\LassoVendor\Sentry\configureScope(
				function ( \LassoVendor\Sentry\State\Scope $scope ) {
					$user_email = get_option( 'admin_email' );
					$user_email = get_option( 'lasso_license_email', $user_email );
					$scope->setUser( array( 'email' => $user_email ), true );
				}
			);

			define( 'SENTRY_LOADED', 'latest' );
		} elseif ( (float) PHP_VERSION < $lasso_php_required ) {
			// ? loading old library to detect error
			// ? Load Sentry/Raven libraries
			// ? https://sentry.io/listen-money-matters/lasso-wordpress-plugin/getting-started/php/
			require_once LASSO_PLUGIN_PATH . '/libs/Raven/Autoloader.php';
			Lasso_Raven_Autoloader::register();
			$client = new Raven_Client( SENTRY_DNS );
			$client->install(); // ? Automatically tracking errors
			$client->setRelease( LASSO_VERSION );    // ? Current Lasso Release

			// ? just send errors come from this plugin
			$client->setSendCallback(
				function( $data ) {
					$exception  = $data['exception'];
					$errors     = $exception['values'][0];
					$stacktrace = $errors['stacktrace'];
					$frames     = $stacktrace['frames'];

					$last_frame = end( $frames );

					if ( strpos( $last_frame['filename'], LASSO_PLUGIN_PATH ) !== false ) {
						return $data;
					}
					return false;
				}
			);

			define( 'SENTRY_LOADED', 'legacy' );
		}
	} catch ( Exception $e ) {
		define( 'SENTRY_LOADED', 'none' );
	}
} else {
	define( 'SENTRY_LOADED', 'none' );
}
