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
define('DB_NAME', 'grouks');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
//define('DB_PASSWORD', 'gem2016');
define('DB_PASSWORD', '');

/** MySQL hostname */
//define('DB_HOST', '172.16.10.114');
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
define('AUTH_KEY',         'ZL!oB$zV5$5<%r%JqRR$EadQ5]0l<GfsdX|&hh716%tX,yLfP0tt0cbb4RHrz lG');
define('SECURE_AUTH_KEY',  'TrX6;u:-zT>,3#xxX4f5Bk=6|,i&ofPqvbwB>^nHamm-^7s>iRUXn$Bor(sZDm/,');
define('LOGGED_IN_KEY',    '-He{}i+[L=ZvB%]Z-&~])7Fxus$bnWvrnp:cVC{aI5= ,OS%Ym%|]$VrR_rWjR!1');
define('NONCE_KEY',        'B=j3[<(PSy3<gOli[wE9&ZMP_[!*{P?h8@MgEF$@K(axLbXn~*r=MY&_:[ce4Av$');
define('AUTH_SALT',        'OXVJjl!l%oFFwN)oDfKNF/J;#03}G^oK]J<3#U@2<l#>jPev$D>K,!RX0<:6fC,,');
define('SECURE_AUTH_SALT', 'dI_sHBRCd=D`}S6p>]lx4=Dl?I[={f.S-%@7LG*J?M[JlP<*?CHby)9I.sPy/+tp');
define('LOGGED_IN_SALT',   '$&-:HBt)<Zmvwu09~RtMC =X6:qAiDd@)cJN(uTHx|ZqFkf6K1) PShqu}v$mdiH');
define('NONCE_SALT',       '|?T/DN)qw$FaUQn#xCm(jvUpvb0A7 FWsj7RN!I{%lJSHPj4]k-m-P7.+%2zwCxC');

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
