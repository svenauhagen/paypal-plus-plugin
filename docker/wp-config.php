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

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'wordpress' );

/** MySQL database password */
define( 'DB_PASSWORD', 'wordpress' );

/** MySQL hostname */
define( 'DB_HOST', 'mariadb' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'hj1n=mC.FLnd+CQ%]/2[;}SKq4?F9>iuJ`t~_Y:l:I/I#:K3XIZ!XS,@$f/XK@,V' );
define( 'SECURE_AUTH_KEY',   'A50XJ Y$clK:J{sVwF;mP/i=%J||]MsL?27VIRcef Hip:t>&Lzh]#f25 -^pf#=' );
define( 'LOGGED_IN_KEY',     'K~eg;}V!N!*`H8YBoX0AmAzuU+]R(p`aymVs.`LQ6j/Lji@`Q|)i2J*-hcw,>G/-' );
define( 'NONCE_KEY',         'TD-pD25zh<JBIfYZk;LYazV`O~2>Q1>e2gY3ZzH+Qk+EyfRPj(BwY&_9[-r;]iYF' );
define( 'AUTH_SALT',         'k`u6/ObUDq@Qf^6R!y<eC#8S`U4A`Lne(~MG34s0K&@/AjV.-}S@0NxLph1}xeY-' );
define( 'SECURE_AUTH_SALT',  '_zccG$Z=y%U7&<S.t?u<u{+W%seh?IPyS,PC?m4-rWp>*U+0NVCMBn|.iv{BU)2`' );
define( 'LOGGED_IN_SALT',    'wS<-}Y5n(~_.}hLiiR$u43&xh7;RLT}H#7tO3%<PdhrA-jrnIA_WV>B26#1GaD]o' );
define( 'NONCE_SALT',        ':~%5:{7-&O}4OOUN`#j.$uD29tsthvN6;-e`=TeF 5k&JP35C+fLRZT?q-k%/U(G' );
define( 'WP_CACHE_KEY_SALT', 'GmSubuG}Erm@?o8fs;gPG_+l5pW%tjw:(>:)IRu6jpp/NUj}:G4x00MC+Ph.s,JI' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
