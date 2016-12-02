<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 20.10.16
 * Time: 14:42
 */

namespace PayPalPlusPlugin;

use PayPalPlusPlugin\WC\PayPalPlusGateway;

class Backend implements Controller {

	/**
	 * @var PayPalPlusGateway
	 */
	private $gateway;
	/**
	 * @var
	 */
	private $file;

	public function __construct( $file, PayPalPlusGateway $gateway ) {

		$this->gateway = $gateway;
		$this->file    = $file;
	}

	public function init() {

		add_action( 'admin_enqueue_scripts', function () {

			$asset_url    = plugin_dir_url( $this->file );
			$min          = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '';
			$admin_script = "{$asset_url}/assets/js/admin{$min}.js";

			wp_enqueue_script( 'paypal-plus-admin', $admin_script, [ 'jquery' ] );
		} );
	}

}