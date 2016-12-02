<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 17:26
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Rest\ApiContext;

class PaymentPatchData {

	/**
	 * @var \WC_Order
	 */
	private $order;
	/**
	 * @var string
	 */
	private $payment_id;
	/**
	 * @var string
	 */
	private $invoice_prefix;
	/**
	 * @var ApiContext
	 */
	private $api_context;

	/**
	 * PaymentPatchData constructor.
	 *
	 * @param \WC_Order  $order
	 * @param string     $payment_id
	 * @param string     $invoice_prefix
	 * @param ApiContext $api_context
	 */
	public function __construct(
		\WC_Order $order,
		$payment_id,
		$invoice_prefix,
		ApiContext $api_context
	) {

		$this->order          = $order;
		$this->payment_id     = $payment_id;
		$this->invoice_prefix = $invoice_prefix;
		$this->api_context    = $api_context;
	}

	/**
	 * @return \WC_Order
	 */
	public function get_order() {

		return $this->order;
	}

	/**
	 * @return string
	 */
	public function get_payment_id() {

		return $this->payment_id;
	}

	/**
	 * @return string
	 */
	public function get_invoice_prefix() {

		return $this->invoice_prefix;
	}

	/**
	 * @return ApiContext
	 */
	public function get_api_context() {

		return $this->api_context;
	}
}