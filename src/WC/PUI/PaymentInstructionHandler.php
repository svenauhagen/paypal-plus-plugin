<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 16.01.17
 * Time: 15:22
 */

namespace PayPalPlusPlugin\WC\PUI;

/**
 * Class PaymentInstructionHandler
 *
 * @package PayPalPlusPlugin\WC\PUI
 */
class PaymentInstructionHandler {

	/**
	 * @var PUIView
	 */
	private $view;
	/**
	 * @var PaymentInstructionData
	 */
	private $data;

	/**
	 * PaymentInstructionHandler constructor.
	 *
	 * @param PaymentInstructionData $data
	 * @param PUIView                $view
	 */
	public function __construct( PaymentInstructionData $data, PUIView $view ) {

		$this->view = $view;
		$this->data = $data;
	}

	public function register() {

		add_action( 'woocommerce_thankyou_paypal_plus', [ $this->view, 'thankyou_page' ], 10, 1 );
		add_action( 'woocommerce_email_before_order_table', [ $this->view, 'email_instructions' ], 10, 3 );
	}

}