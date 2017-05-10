<?php
namespace WCPayPalPlus;

use WCPayPalPlus\WC\IPN;
use WCPayPalPlus\WC\PayPalPlusGateway;

/**
 * Created by PhpStorm.
 * User: biont
 * Date: 20.10.16
 * Time: 12:39
 */
class Plugin {

	/**
	 * Plugin filename.
	 *
	 * @var string
	 */
	private $file;
	/**
	 * Gateway ID.
	 *
	 * @var string
	 */
	private $gateway_id = 'paypal_plus';
	/**
	 * Payment Gateway object.
	 *
	 * @var PayPalPlusGateway
	 */
	private $gateway;

	/**
	 * Plugin constructor.
	 *
	 * @param string $file Plugin filename.
	 */
	public function __construct( $file ) {

		$this->gateway = new PayPalPlusGateway(
			$this->gateway_id,
			__( 'PayPal Plus', 'woo-paypalplus' )
		);
		$this->gateway->register();

		$this->file = $file;
	}

	/**
	 * Initialize the Plugin and configure all needed controllers.
	 */
	public function init() {

		$this->get_common_controller()
		     ->init();

		$this->get_request_controller()
		     ->init();
	}

	/**
	 * Returns the Controller that runs both on frontend and backend.
	 *
	 * @return Controller
	 */
	public function get_common_controller() {

		return new Common( $this->gateway );

	}

	/**
	 * Returns either a BackendController or a FrontendController, based on is_admin().
	 *
	 * @return Controller
	 */
	private function get_request_controller() {

		return is_admin() ? new Backend( $this->file, $this->gateway ) : new Frontend( $this->gateway );

	}
}
