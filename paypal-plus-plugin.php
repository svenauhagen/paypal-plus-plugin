<?php # -*- coding: utf-8 -*-

/**
 * Plugin Name: PayPal Plus Plugin
 * Description: Official WordPress plugin for PayPal Plus
 * Plugin URI:  TODO
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com/
 * Version:     dev-master
 * License:     MIT
 * Text Domain: paypal-plus-plugin
 */

namespace PayPalPlusPlugin;

/**
 * Check if we're already autoloaded by some external autloader
 * If not, load our own
 */
if ( ! class_exists( 'PayPalPlusPlugin\\Plugin' ) ) {
	if ( file_exists( $autoloader = __DIR__ . '/vendor/autoload.php' ) ) {
		/** @noinspection PhpIncludeInspection */
		require $autoloader;
	} else {
		//Throw Error here
		return;
	}
}
add_action( 'plugins_loaded', function () {

	( new Plugin( __FILE__ ) )->init();
} );
