<?php
/**
 * Plugin Name: PayPal Plus for WooCommerce
 * Description: Official WordPress plugin for PayPal Plus
 * Author:      Inpsyde GmbH, Moritz Meißelbach
 * Author URI:  http://inpsyde.com/
 * Version:     1.0
 * License:     MIT
 * Text Domain: paypalplus-woocommerce
 * Domain Path: /languages/
 */

namespace WCPayPalPlus;

add_action( 'plugins_loaded', function () {

	/**
	 * Check if we're already autoloaded by some external autloader
	 * If not, load our own
	 */
	if ( ! class_exists( 'WCPayPalPlus\\Plugin' ) ) {

		if ( file_exists( $autoloader = __DIR__ . '/vendor/autoload.php' ) ) {
			/** @noinspection PhpIncludeInspection */
			require $autoloader;
		} else {

			add_action( 'admin_notices', function () {

				$class   = 'notice notice-error';
				$message = __( 'Could not find a working autoloader for PayPal Plus.', 'paypalplus-woocommerce' );

				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			} );

			return;
		}
	}

	/**
	 * Check if WooCommerce is active. Bail if it's not.
	 */
	if ( ! class_exists( 'WooCommerce' ) ) {

		add_action( 'admin_notices', function () {

			$class   = 'notice notice-error';
			$message = __( 'PayPay Plus requires WooCommerce to be active.', 'paypalplus-woocommerce' );

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} );

		return;
	}

	/**
	 * Now we're good to go.
	 */
	( new Plugin( __FILE__ ) )->init();
} );
