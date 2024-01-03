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

define( 'WP_DEBUG', true );
define('FORCE_SSL', false);
define('FORCE_SSL_ADMIN', false);
define('WP_SITEURL','http://localhost/apkafe/');
define('WP_HOME','http://localhost/apkafe/');

 // Added by WP Rocket
define('FS_METHOD', 'direct');

error_reporting(1);
@ini_set('display_errors', 1);

// ** MySQL settings - You can get this info from your web host ** //


/** The name of the database for WordPress */
define( 'DB_NAME', 'apkafe' );

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

define('AUTH_KEY',         'oy||=D>:{gUd51P4m^wi|l8=_$GBl2w(HC*vy@9nk:3R0Xijv-E1FxY+jXO jE)]');
define('SECURE_AUTH_KEY',  'HilEfk^}!|c`p$ZNNU-iZ~04pFbW&1HJ@Sl~5H4t9+d$;hhZvx_U|jy0^j2n-#p6');
define('LOGGED_IN_KEY',    'SXS.a+74@JBm5;)a$`p<|;Co4IT&!)bu0C#nVq97#w/O;YW5##$wLqJo0To,2HM8');
define('NONCE_KEY',        'a;@._8j+1!1Btp.IjHR-F<`~}0#F;PV/Ixu~9ChZ7@6,kq_FM,&.XoO/guz125+:');
define('AUTH_SALT',        '*Fd@R;~O_lPM5XfqFzFGqiD= HhcY[_U.U*Kh-+S[c|j2;U]d(+Di|(@;$jP 5yc');
define('SECURE_AUTH_SALT', '#e+A.H)m51^R+#;H>]m,!B=$)J$B6 o=<eyaA+A#4XI^|O}5iPH~l@`TsmE}5^PL');
define('LOGGED_IN_SALT',   'SQB?&[jFzD{Ie[7$|I&;omVUBYVm5l}$nrNKrRfvbf]{EasF=5K}{-=A#icxDcG=');
define('NONCE_SALT',       'R;bP$?m3mDWQ,G+4b)p%M3A4$y+y|+GEKLJpn$*j@lrb+|kuI<vaxuo_B+@~WP/v');


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
