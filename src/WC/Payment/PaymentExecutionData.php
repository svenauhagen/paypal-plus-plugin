<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 18:25
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;

class PaymentExecutionData {

	/**
	 * @var Payment
	 */
	private $payment;
	/**
	 * @var string
	 */
	private $payer_id;
	/**
	 * @var string
	 */
	private $payment_id;
	/**
	 * @var ApiContext
	 */
	private $context;
	/**
	 * @var \WC_Order
	 */
	private $order;

	public function __construct( \WC_Order $order, $payer_id, $payment_id, ApiContext $context ) {

		$this->payer_id   = $payer_id;
		$this->payment_id = $payment_id;
		$this->context    = $context;
		$this->order      = $order;
	}

	public function is_PUI() {

		$instructions = $this->get_payment_instruction();

		return ( isset( $instructions ) && ! empty( $instructions ) );

	}

	/**
	 * @return bool;
	 */
	public function is_approved() {

		return $this->get_payment_state() === 'approved';
	}

	/**
	 * @return \PayPal\Api\PaymentInstruction
	 */
	public function get_payment_instruction() {

		return $this->get_payment()
		            ->getPaymentInstruction();

	}

	/**
	 * Returns the sale object of the payment
	 *
	 * @return \PayPal\Api\Sale
	 */
	public function get_sale() {

		$transactions     = $this->get_payment()
		                         ->getTransactions();
		$relatedResources = $transactions[0]->getRelatedResources();

		return $relatedResources[0]->getSale();
	}

	/**
	 * @return string
	 */
	public function get_payment_state() {

		return $this->get_payment()->state;
	}

	/**
	 * @return Payment
	 */
	public function get_payment() {

		if ( is_null( $this->payment ) ) {
			$this->payment = Payment::get( $this->payment_id, $this->context );

		}

		return $this->payment;
	}

	/**
	 * @return PaymentExecution
	 */
	public function get_payment_execution() {

		$execution = new PaymentExecution();
		$execution->setPayerId( $this->payer_id );

		return $execution;

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
	public function get_payer_id() {

		return $this->payer_id;
	}

	/**
	 * @return ApiContext
	 */
	public function get_context() {

		return $this->context;
	}

	/**
	 * @return \WC_Order
	 */
	public function get_order() {

		return $this->order;
	}
}