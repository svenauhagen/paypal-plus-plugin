<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 06.12.16
 * Time: 09:37
 */

namespace WCPayPalPlus\WC\Payment;

use WCPayPalPlus\WC\RequestSuccessHandler;

/**
 * Class PaymentExecutionSuccess
 *
 * @package WCPayPalPlus\WC\Payment
 */
class PaymentExecutionSuccess implements RequestSuccessHandler {

	/**
	 * Payment Data from successful Execution.
	 *
	 * @var PaymentExecutionData
	 */
	private $data;

	/**
	 * PaymentExecutionSuccess constructor.
	 *
	 * @param PaymentExecutionData $data Payment Data from successful Execution.
	 */
	public function __construct( PaymentExecutionData $data ) {

		$this->data = $data;
	}

	/**
	 * Execute!
	 */
	public function execute() {

		$order = $this->data->get_order();
		if ( $this->data->is_approved() ) {

			$this->update_order();

			WC()->cart->empty_cart();
			$redirect_url = $order->get_checkout_order_received_url();

		} else {
			$notice = sprintf(
				__( 'There was an error executing the payment. Payment state: %s', 'woo-paypalplus' ),
				$this->data->get_payment_state()
			);
			wc_add_notice( $notice, 'error' );
			$redirect_url = wc_get_cart_url();
		}
		// Todo: Refactor so that we can properly test this class.
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Update Order details.
	 */
	private function update_order() {

		$sale    = $this->data->get_sale();
		$sale_id = $sale->getId();
		$order   = $this->data->get_order();

		if ( $sale->getState() === 'pending' ) {
			$note = sprintf(
				__( 'PayPal Reason code: %s.', 'woo-paypalplus' ),
				$sale->getReasonCode()
			);
			$order->add_order_note( $note );
			$order->update_status( 'on-hold' );

		} elseif ( $sale->getState() === 'completed' && ! $this->data->is_pui() ) {

			$order->add_order_note( __( 'PayPal Plus payment completed', 'woo-paypalplus' ) );
			$order->payment_complete( $sale_id );
			$note = sprintf(
				__( 'PayPal Plus payment approved! Transaction ID: %s', 'woo-paypalplus' ),
				$sale_id
			);
			$order->add_order_note( $note );
			WC()->cart->empty_cart();
		} else {
			$order->update_status( 'on-hold', __( 'Awaiting payment', 'woocommerce' ) );
			wc_reduce_stock_levels( $order->get_id() );
		}

		if ( $this->data->is_pui() ) {
			$instruction      = $this->data->get_payment_instruction();
			$instruction_type = $instruction->getInstructionType();
			if ( 'PAY_UPON_INVOICE' === $instruction_type ) {
				$this->update_payment_data( $sale_id );
			}
		}

		if ( $this->should_update_address() ) {
			$this->update_billing_address();

		}

	}

	/**
	 * Update order post meta with payment information.
	 *
	 * @param string $sale_id PayPal Payment ID.
	 */
	private function update_payment_data( $sale_id ) {

		$order = $this->data->get_order();

		$payment_instruction = $this->data->get_payment_instruction();
		$reference_number    = $payment_instruction->getReferenceNumber();
		$payment_due_date    = $payment_instruction->getPaymentDueDate();

		$recipient_banking_instruction = $payment_instruction->getRecipientBankingInstruction();
		$bank_name                     = $recipient_banking_instruction->getBankName();
		$account_holder_name           = $recipient_banking_instruction->getAccountHolderName();
		$iban                          = $recipient_banking_instruction->getInternationalBankAccountNumber();
		$bank_identifier_code          = $recipient_banking_instruction->getBankIdentifierCode();

		$instruction_data['reference_number']                                                   = $reference_number;
		$instruction_data['instruction_type']                                                   = 'PAY_UPON_INVOICE';
		$instruction_data['recipient_banking_instruction']['bank_name']                         = $bank_name;
		$instruction_data['recipient_banking_instruction']['account_holder_name']               = $account_holder_name;
		$instruction_data['recipient_banking_instruction']['international_bank_account_number'] = $iban;
		$instruction_data['recipient_banking_instruction']['bank_identifier_code']              = $bank_identifier_code;

		$meta_data = [
			'reference_number'                  => $reference_number,
			'instruction_type'                  => 'PAY_UPON_INVOICE',
			'payment_due_date'                  => $payment_due_date,
			'bank_name'                         => $bank_name,
			'account_holder_name'               => $account_holder_name,
			'international_bank_account_number' => $iban,
			'bank_identifier_code'              => $bank_identifier_code,
			'_payment_instruction_result'       => $instruction_data,
			'_transaction_id'                   => $sale_id,
		];

		foreach ( $meta_data as $key => $value ) {
			$order->add_meta_data( $key, $value );

		}
		$order->save();

	}

	/**
	 * Check if the customer's address needs to be updated.
	 *
	 * @return bool
	 */
	private function should_update_address() {

		return ! empty( $this->data->get_payment()->payer->payer_info->billing_address->line1 );

	}

	/**
	 * Update the Order billing address
	 */
	private function update_billing_address() {

		$payment         = $this->data->get_payment();
		$order           = $this->data->get_order();
		$billing_address = [
			'first_name' => $payment->payer->payer_info->first_name,
			'last_name'  => $payment->payer->payer_info->last_name,
			'address_1'  => $payment->payer->payer_info->billing_address->line1,
			'address_2'  => $payment->payer->payer_info->billing_address->line2,
			'city'       => $payment->payer->payer_info->billing_address->city,
			'state'      => $payment->payer->payer_info->billing_address->state,
			'postcode'   => $payment->payer->payer_info->billing_address->postal_code,
			'country'    => $payment->payer->payer_info->billing_address->country_code,
		];
		$order->set_address( $billing_address, $type = 'billing' );
	}

	/**
	 * Allow the implementing class to setup hooks
	 */
	public function register() {
		// Nothing to do.
	}
}
