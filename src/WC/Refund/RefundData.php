<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 10:23
 */

namespace PayPalPlusPlugin\WC\Refund;

use PayPal\Api\Amount;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;
use PayPal\Rest\ApiContext;

/**
 * Class RefundData
 *
 * Bridge between WooCommcerce and PayPal objects.
 * Provides WooCommcerce with the objects needed to perform a refund
 *
 * @package PayPalPlusPlugin\WC
 */
class RefundData {

	/**
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * @var float
	 */
	private $amount;
	/**
	 * @var ApiContext
	 */
	private $context;
	private $reason;

	public function __construct( \WC_Order $order, $amount, $reason, ApiContext $context ) {

		$this->order   = $order;
		$this->amount  = floatval( $amount );
		$this->context = $context;
		$this->reason  = $reason;
	}

	/**
	 * @return float
	 */
	public function get_amount() {

		return $this->amount;

	}

	/**
	 * @return string
	 */
	public function get_reason() {

		return $this->reason;

	}

	/**
	 * @return Sale
	 */
	public function get_sale() {

		return Sale::get( $this->order->get_transaction_id(), $this->context );
	}

	/**
	 * @return RefundRequest
	 */
	public function get_refund() {

		$amt = new Amount();
		$amt->setCurrency( $this->order->get_order_currency() );
		$amt->setTotal( $this->number_format( $this->amount ) );
		$refund = new RefundRequest();
		$refund->setAmount( $amt );

		return $refund;
	}

	/**
	 * @param $transaction_id
	 *
	 * @return RefundSuccess
	 */
	public function get_success_handler( $transaction_id ) {

		return new RefundSuccess( $this->order, $transaction_id, $this->reason );

	}

	/**
	 * @param $price
	 *
	 * @return string
	 */
	private function number_format( $price ) {

		$decimals = 2;

		if ( in_array( get_woocommerce_currency(), array( 'HUF', 'JPY', 'TWD' ) ) ) {
			$decimals = 0;
		}

		return number_format( $price, $decimals, '.', '' );

	}
}