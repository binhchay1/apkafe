<?php
/**
 * Declare polyfill. Make deprecated functions can work in new PHP versions.
 * Refer: Symfony\Polyfill
 *
 * @package lasso polyfill
 */

require_once LASSO_PLUGIN_PATH . '/libs/lasso/class-normalizer.php';

// ? PHP 8.0 compatible
if (\PHP_VERSION_ID >= 80000 && \PHP_VERSION_ID < 81000) {
    
}

// ? PHP 8.1 compatible
if (\PHP_VERSION_ID >= 81000 && \PHP_VERSION_ID < 82000) {
    
}

if ( ! interface_exists( 'Stringable' ) ) {
    interface Stringable
    {
        /**
         * @return string
         */
        public function __toString();
    }
}
