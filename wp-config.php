<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wpmedia-devtest-db');

/** Database username */
define('DB_USER', 'root');

/** Database password */
define('DB_PASSWORD', '');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'kleyP*g=.rkJ4PVj Z}CJ?5?^K{Jl^/AqM)ff~W9kUM/2H23X7(UhfuLqXO49hY`');
define('SECURE_AUTH_KEY',  'I%?wlJ_{jhbSYUFuJW?WGlO,+*gS=VqB0?Z,<(k;zyT0~LAb<VL*#_$83UI=k7hn');
define('LOGGED_IN_KEY',    '.*9J&``kLIkXM !Pe--=_mi>YTMSyzj c4;zB)PpVNL)VT}$Ond8]8TZXf<@29Sw');
define('NONCE_KEY',        'u]s_ECkou9x9|]y6s.*>}[_?3U}&Ti%I|}YJ*fB_x74=Ar5///}.&)Z/uFwYb@ z');
define('AUTH_SALT',        ' %wJKCTL+jk4luMYMOx/D:_zJ85Q>r y3tw%eY1KZ7/SEFU);G$S[b7dlTsmuCZ^');
define('SECURE_AUTH_SALT', 'L1h_vkhmdb*pQ&>W69%u~A`){a]BDM;_SftdY8;Y h1EftP{qRpYt@.dAN{k4_M<');
define('LOGGED_IN_SALT',   '_5rCN(oir+*ErtkL+zX7/{$dZg:3jA2gZsqu|n~/B!9MQZ)-cZN;3hay8D;lNNM^');
define('NONCE_SALT',       '>W{@AE7$1C<Tao0ys;eY_:6<]!4hc]lg_Mh.VXrv4>{^g+[XCIf@0$SP/9|^+ayY');

/**#@-*/

/**
 * WordPress database table prefix.
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
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
