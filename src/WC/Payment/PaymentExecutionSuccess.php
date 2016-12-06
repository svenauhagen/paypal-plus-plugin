<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 06.12.16
 * Time: 09:37
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Payment;
use PayPal\Api\PaymentInstruction;
use PayPalPlusPlugin\WC\RequestSuccessHandler;

class PaymentExecutionSuccess implements RequestSuccessHandler {

	/**
	 * @var PaymentExecutionData
	 */
	private $data;

	public function __construct( PaymentExecutionData $data ) {

		$this->data = $data;
	}

	public function execute() {

		$order = $this->data->get_order();
		if ( $this->data->is_approved() ) {

			$this->update_order();

			WC()->cart->empty_cart();
			$redirect_url = $order->get_checkout_order_received_url();

		} else {
			wc_add_notice( __( 'Error Payment state:' . $this->data->get_payment_state(), 'woo-paypal-plus' ),
				'error' );
			$redirect_url = wc_get_cart_url();
		}
		wp_redirect( $redirect_url );
		exit;
	}

	public function update_order() {

		$sale    = $this->data->get_sale();
		$sale_id = $sale->getId();
		$order   = $this->data->get_order();

		if ( $sale->getState() == 'pending' ) {
			$order->add_order_note( sprintf( __( 'PayPal Reason code: %s.', 'woo-paypal-plus' ),
				$sale->getReasonCode() ) );
			$order->update_status( 'on-hold' );

		} elseif ( $sale->getState() == 'completed' && ! $this->data->is_PUI() ) {
			$order->add_order_note( __( 'PayPal Plus payment completed', 'woo-paypal-plus' ) );
			$order->payment_complete( $sale_id );
			$order->add_order_note( sprintf( __( 'PayPal Plus payment approved! Transaction ID: %s',
				'woo-paypal-plus' ), $sale_id ) );
			WC()->cart->empty_cart();
		} else {
			$order->update_status( 'on-hold', __( 'Awaiting payment', 'woocommerce' ) );
			$order->reduce_order_stock();
		}

		if ( $this->data->is_PUI() ) {
			$instruction      = $this->data->get_payment_instruction();
			$instruction_type = $instruction->getInstructionType();
			if ( $instruction_type == 'PAY_UPON_INVOICE' ) {
				$this->update_payment_data( $order, $instruction );
			}
		}

		$this->update_billing_address( $order, $this->data->get_payment() );

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
}