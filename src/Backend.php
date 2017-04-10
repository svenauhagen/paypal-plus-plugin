<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 20.10.16
 * Time: 14:42
 */

namespace WCPayPalPlus;

use WCPayPalPlus\WC\PayPalPlusGateway;

/**
 * Class Backend
 *
 * @package WCPayPalPlus
 */
class Backend implements Controller {

	/**
	 * Gateway class
	 *
	 * @var PayPalPlusGateway
	 */
	private $gateway;
	/**
	 * Main Plugin file path
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Backend constructor.
	 *
	 * @param string            $file    Main plugin filepath.
	 * @param PayPalPlusGateway $gateway Gateway class.
	 */
	public function __construct( $file, PayPalPlusGateway $gateway ) {

		$this->gateway = $gateway;
		$this->file    = $file;
	}

	/**
	 * Setup hooks
	 */
	public function init() {

		add_action( 'admin_enqueue_scripts', function () {

			$asset_url    = plugin_dir_url( $this->file );
			$min          = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '';
			$admin_script = "{$asset_url}/assets/js/admin{$min}.js";

			wp_enqueue_script( 'paypalplus-woocommerce-admin', $admin_script, [ 'jquery' ] );
		} );
	}

}