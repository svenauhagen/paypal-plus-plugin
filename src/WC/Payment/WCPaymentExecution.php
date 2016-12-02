<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 09.11.16
 * Time: 12:55
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\PaymentInstruction;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class WCPaymentExecution {

	/**
	 * @var ApiContext
	 */
	private $context;

	/**
	 * @var int
	 */
	private $payer_id;
	private $payment_id;

	/**
	 * @var Payment
	 */
	private $payment;

	public function __construct( $payer_id, $payment_id, ApiContext $context ) {

		$this->context    = $context;
		$this->payer_id   = $payer_id;
		$this->payment_id = $payment_id;
	}

	public function execute() {

		$execution = new PaymentExecution();
		$execution->setPayerId( WC()->session->PayerID );

		$payment = Payment::get( $this->payment_id, $this->context );
		$payment->execute( $execution, $this->context );

		return $payment;
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

	public function update_order( \WC_Order $order ) {

		$sale             = $this->get_sale();
		$sale_id          = $sale->getId();
		$instruction_type = $this->get_payment_instruction()
		                         ->getInstructionType();

		if ( $sale->getState() == 'pending' ) {
			$order->add_order_note( sprintf( __( 'PayPal Reason code: %s.', 'woo-paypal-plus' ),
				$sale->getReasonCode() ) );
			$order->update_status( 'on-hold' );

		} elseif ( $sale->getState() == 'completed' && empty ( $instruction_type ) ) {
			$order->add_order_note( __( 'PayPal Plus payment completed', 'woo-paypal-plus' ) );
			$order->payment_complete( $sale_id );
			$order->add_order_note( sprintf( __( 'PayPal Plus payment approved! Transaction ID: %s',
				'woo-paypal-plus' ), $sale_id ) );
			WC()->cart->empty_cart();
		} else {
			$order->update_status( 'on-hold', __( 'Awaiting payment', 'woocommerce' ) );
			$order->reduce_order_stock();
		}

		if ( $this->is_PUI() ) {
			$instruction      = $this->get_payment_instruction();
			$instruction_type = $instruction->getInstructionType();
			if ( $instruction_type == 'PAY_UPON_INVOICE' ) {
				$this->update_payment_data( $order, $instruction );
			}
		}

		$this->update_billing_address( $order, $this->get_payment() );

	}

	public function update_billing_address( \WC_Order $order, Payment $payment = NULL ) {

		if ( ! empty( $payment->payer->payer_info->billing_address->line1 ) ) {
			$billing_address = array(
				'first_name' => $payment->payer->payer_info->first_name,
				'last_name'  => $payment->payer->payer_info->last_name,
				'address_1'  => $payment->payer->payer_info->billing_address->line1,
				'address_2'  => $payment->payer->payer_info->billing_address->line2,
				'city'       => $payment->payer->payer_info->billing_address->city,
				'state'      => $payment->payer->payer_info->billing_address->state,
				'postcode'   => $payment->payer->payer_info->billing_address->postal_code,
				'country'    => $payment->payer->payer_info->billing_address->country_code,
			);
			$order->set_address( $billing_address, $type = 'billing' );
		}
	}

	public function update_payment_data( \WC_Order $order, PaymentInstruction $payment_instruction ) {

		$reference_number = $payment_instruction->getReferenceNumber();
		$payment_due_date = $payment_instruction->getPaymentDueDate();

		$RecipientBankingInstruction       = $payment_instruction->getRecipientBankingInstruction();
		$bank_name                         = $RecipientBankingInstruction->getBankName();
		$account_holder_name               = $RecipientBankingInstruction->getAccountHolderName();
		$international_bank_account_number = $RecipientBankingInstruction->getInternationalBankAccountNumber();
		$bank_identifier_code              = $RecipientBankingInstruction->getBankIdentifierCode();

		$instruction_data['reference_number']                                                   = $reference_number;
		$instruction_data['instruction_type']                                                   = 'PAY_UPON_INVOICE';
		$instruction_data['recipient_banking_instruction']['bank_name']                         = $bank_name;
		$instruction_data['recipient_banking_instruction']['account_holder_name']               = $account_holder_name;
		$instruction_data['recipient_banking_instruction']['international_bank_account_number'] = $international_bank_account_number;
		$instruction_data['recipient_banking_instruction']['bank_identifier_code']              = $bank_identifier_code;

		update_post_meta( $order->id, 'reference_number', $reference_number );
		update_post_meta( $order->id, 'instruction_type', 'PAY_UPON_INVOICE' );
		update_post_meta( $order->id, 'payment_due_date', $payment_due_date );
		update_post_meta( $order->id, 'bank_name', $bank_name );
		update_post_meta( $order->id, 'account_holder_name', $account_holder_name );
		update_post_meta( $order->id, 'international_bank_account_number',
			$international_bank_account_number );
		update_post_meta( $order->id, 'bank_identifier_code', $bank_identifier_code );
		update_post_meta( $order->id, 'payment_due_date', $payment_due_date );
		update_post_meta( $order->id, '_payment_instruction_result', $instruction_data );

	}

	/**
	 * Returns the sale object of the payment
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

	public function get_payment() {

		if ( is_null( $this->payment ) ) {
			$this->payment = $this->execute();
		}

		return $this->payment;
	}
}