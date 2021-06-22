<?php
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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'pivot');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'B<yxuqi+PB{aJ5UdIg]RbUf$sfN(xGsC+g# -#5!8)[e<nY7]yJv/,E<UAQ$/(}V');
define('SECURE_AUTH_KEY',  '2N6)L~x^=W.]:a7W]/Vee1,iQ_T~(%K&zBOnn5:!|qlI |I^zAyp6R37x{,ZVQkW');
define('LOGGED_IN_KEY',    '/N?Tf2{j]i8&=3Nr K4(HT2~iiVZr+Vw5&q*4T>7v8H6/|w~KPnCDzOX~}DZm@BL');
define('NONCE_KEY',        'v+QqM_W~]/ q?KC~vC]I(I5b]~*rV^#UlkbfG,c6p0q22aP`V&4&DGyi7-V|`b,1');
define('AUTH_SALT',        ' ^e=]ng[p]{t=CX%G?Kh.=q fW=5Isz7UY~oQu psD=YH&JY6K(K=]b0k=|I]Q[j');
define('SECURE_AUTH_SALT', 'k~8nSzLI%[L_r=%6xS3Rx|`crzyl1dj2c19tYmpViUepJ5UF4SA>F39;J~{@dz$S');
define('LOGGED_IN_SALT',   '9l5x8_u<m38yUs]m=N-O9d0*j:Fc1x6KkT:$c,N.2]uFCIj]{*Z6dhDHI2g|gcup');
define('NONCE_SALT',       'eO&IUI<aiU+$giS<d{2Rd>H_Kd#rC4)=%?W5CyqZmD{M]z*0Leu=HRyK%wZYF+-E');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
