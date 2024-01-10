<?php

/** Enable W3 Total Cache */
 // Added by WP Rocket


/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

/*

// Enable WP_DEBUG mode
define( 'WP_DEBUG', true );

// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );

// Disable display of errors and warnings
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 0 );

// Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
define( 'SCRIPT_DEBUG', true );


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

*/

define('WP_DEBUG', false);
define('FORCE_SSL', false);
define('FORCE_SSL_ADMIN', false);
define('WP_SITEURL','http://localhost/apkafe/');
define('WP_HOME','http://localhost/apkafe/');

error_reporting(1);
@ini_set('display_errors', 1);

// ** MySQL settings - You can get this info from your web host ** //


/** The name of the database for WordPress */
define( 'DB_NAME', 'apkafe-test' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */

define( 'AUTH_KEY',         '.#&GV[DMcnrP<<>W9R1Kk.7E!W*}D[XzjMI?i3Q~z`GD}Fdg}QJ:[E;6TB!CV#8S' );
define( 'SECURE_AUTH_KEY',  'kKKZ>PkEsN9.tO2Gp@I><vg.Osti[cFRYuMMOsIDf8wXKIq,L%BZ|W>I2fF|p+Gs' );
define( 'LOGGED_IN_KEY',    'CWt%1K6!dwp{u&BI,jy{KZcCZHAUFv?>{]U|D1RX_;b$gs^MI`)Q!0H*aU_S]t|#' );
define( 'NONCE_KEY',        '_T*;D{?./t<jTFJ;6bUr1 Z~74iC<dMgrIuZkcH0HIezZ&ecjvk=nv1yOwnJz(9.' );
define( 'AUTH_SALT',        'T6_?<5Ob.r7O7zE|[!zm_c#>CNIi5HpW|lT4^aX dJi):>fUAZrb#%J|;|2`[#L=' );
define( 'SECURE_AUTH_SALT', '{s*5mUD6+:ZLn~8$uX)*pp`),__^|-<R{~NO m]s,-)_DuP@yE9v:a7hF3Q#ZSgG' );
define( 'LOGGED_IN_SALT',   ':Ke*7`{wn@tO(yP-5A0S~=eat}+-[:9(ULP#nJpuj:<fE&gW$;yLD53[c3L(L<wL' );
define( 'NONCE_SALT',       'oyXGuKgS1O.YbwErM~S9k>5RP=G<Z5! 4Esf$=IU$+ID_)CQ+Ak/@i~qUv;yzC-f' );


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
//define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
