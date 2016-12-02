<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 25.10.16
 * Time: 15:45
 */

namespace PayPalPlusPlugin;

use PayPalPlusPlugin\WC\PayPalPlusGateway;

class Common implements Controller {

	/**
	 * @var PayPalPlusGateway
	 */
	private $gateway;

	public function __construct(PayPalPlusGateway $gateway) {

		$this->gateway = $gateway;
	}

	public function init() {

		add_filter( 'woocommerce_payment_gateways', [ $this, 'add_paypal_plus' ] );
	}

	public function add_paypal_plus( $methods ) {

		$methods[] = $this->gateway;

		return $methods;
	}
}