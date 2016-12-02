<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 20.10.16
 * Time: 14:43
 */

namespace PayPalPlusPlugin;

use PayPalPlusPlugin\WC\PayPalPlusGateway;

class Frontend implements Controller {

	/**
	 * @var PayPalPlusGateway
	 */
	private $gateway;

	public function __construct( PayPalPlusGateway $gateway ) {

		$this->gateway = $gateway;
	}

	public function init() {

		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 100 );
	}

	public function frontend_scripts() {

		//TODO check if is checkout
		wp_enqueue_script(
			'paypal_plus',
			'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',
			[],
			'1.0', FALSE
		);

	}
}