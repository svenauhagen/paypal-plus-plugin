<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 25.10.16
 * Time: 15:45
 */

namespace WCPayPalPlus;

use WCPayPalPlus\WC\PayPalPlusGateway;

/**
 * Class Common
 *
 * @package WCPayPalPlus
 */
class Common implements Controller {

	/**
	 * The Payment Gateway.
	 *
	 * @var PayPalPlusGateway
	 */
	private $gateway;

	/**
	 * Common constructor.
	 *
	 * @param PayPalPlusGateway $gateway The Payment Gateway.
	 */
	public function __construct( PayPalPlusGateway $gateway ) {

		$this->gateway = $gateway;
	}

	/**
	 * Setup hooks.
	 */
	public function init() {

		add_filter( 'woocommerce_payment_gateways', function ( $methods ) {

			$methods[] = $this->gateway;

			return $methods;
		} );
	}
}
