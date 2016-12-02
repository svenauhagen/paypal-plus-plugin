<?php
namespace PayPalPlusPlugin;

use PayPalPlusPlugin\WC\PayPalPlusGateway;

/**
 * Created by PhpStorm.
 * User: biont
 * Date: 20.10.16
 * Time: 12:39
 */
class Plugin {

	private $file;
	/**
	 * @var PayPalPlusGateway
	 */
	private $gateway;

	/**
	 * Plugin constructor.
	 *
	 * @param $file
	 */
	public function __construct( $file ) {

		$this->gateway = $this->get_gateway();
		$this->gateway->register();

		$this->file = $file;
	}

	/**
	 *
	 */
	public function init() {

		$this->get_common_controller()
		     ->init();

		$this->get_request_controller()
		     ->init();
	}

	/**
	 * return Controller
	 */
	public function get_common_controller() {

		return new Common( $this->gateway );

	}

	private function get_gateway() {

		return new PayPalPlusGateway(
			'paypal_plus',
			__( 'PayPal Plus', 'paypal-plus-plugin' )
		);
	}

	/**
	 * @return Controller
	 */
	private function get_request_controller() {

		return is_admin() ? new Backend( $this->file, $this->gateway ) : new Frontend( $this->gateway );

	}
}