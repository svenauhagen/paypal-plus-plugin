<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 20.10.16
 * Time: 14:43
 */

namespace WCPayPalPlus;

use WCPayPalPlus\WC\PayPalPlusGateway;

/**
 * Class Frontend
 *
 * @package WCPayPalPlus
 */
class Frontend implements Controller {

	/**
	 * The PPPlus Payment Gateway.
	 *
	 * @var PayPalPlusGateway
	 */
	private $gateway;

	/**
	 * Frontend constructor.
	 *
	 * @param PayPalPlusGateway $gateway The Payment Gateway.
	 */
	public function __construct( PayPalPlusGateway $gateway ) {

		$this->gateway = $gateway;
	}

	/**
	 * Initialize the controller and setup all hooks
	 */
	public function init() {

		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 100 );
	}

	/**
	 * Register all needed frontend scripts.
	 */
	public function frontend_scripts() {

		if ( is_checkout() || is_checkout_pay_page() ) {
			wp_enqueue_script(
				'ppplus-js',
				'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',
				[],
				'1.0',
				false
			);
		}

	}
}
