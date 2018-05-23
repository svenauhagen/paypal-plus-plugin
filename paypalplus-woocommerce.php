<?php
/**
 * Plugin Name: PayPal Plus for WooCommerce
 * Description: PayPal Plus - the official WordPress Plugin for WooCommerce
 * Author:      Inpsyde GmbH
 * Author URI:  https://inpsyde.com/
 * Version:     1.0.8
 * WC requires at least: 3.0.0
 * WC tested up to: 3.4.0
 * License:     MIT
 * Text Domain: woo-paypalplus
 * Domain Path: /languages/
 */

namespace WCPayPalPlus;

add_action( 'plugins_loaded', function () {

	load_plugin_textdomain( 'woo-paypalplus', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	/**
	 * Spawn a little helper to put admin notices on the WP Admin panel.
	 *
	 * @param $message
	 */
	$admin_notice = function ( $message ) {

		add_action( 'admin_notices', function () use ( $message ) {

			$class = 'notice notice-error';
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} );
	};

	$min_php_version = '5.6';
	if ( ! version_compare( phpversion(), $min_php_version, '>=' ) ) {
		$admin_notice(
			sprintf(
				__( 'PayPal Plus requires PHP version %1$1s or higher. You are running version %2$2s ' ),
				$min_php_version,
				phpversion()
			)
		);

		return;
	}

	/**
	 * Check if we're already autoloaded by some external autloader
	 * If not, load our own
	 */
	if ( ! class_exists( 'WCPayPalPlus\\Plugin' ) ) {

		if ( file_exists( $autoloader = __DIR__ . '/vendor/autoload.php' ) ) {
			/** @noinspection PhpIncludeInspection */
			require $autoloader;
		} else {

			$admin_notice( __( 'Could not find a working autoloader for PayPal Plus.', 'woo-paypalplus' ) );

			return;
		}
	}

	/**
	 * Check if WooCommerce is active. Bail if it's not.
	 */
	if ( ! class_exists( 'WooCommerce' ) ) {

		$admin_notice( __( 'PayPal Plus requires WooCommerce to be active.', 'woo-paypalplus' ) );

		return;
	}

	if ( version_compare( WC()->version, '3.0.0', '<=' ) ) {
		add_action( 'admin_notices', function () {

			$class   = 'notice notice-error';
			$message = __( 'PayPal Plus requires WooCommerce version 3.0 or higher .', 'woo-paypalplus' );

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} );

		return;
	}

	/**
	 * Now we're good to go.
	 */
	$plugin = new Plugin( __FILE__ );
	$plugin->init();
} );
