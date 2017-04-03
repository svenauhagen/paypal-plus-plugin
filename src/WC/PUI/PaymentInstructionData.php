<?php

namespace PayPalPlusPlugin\WC\PUI;

/**
 * Class PaymentInstructionData
 *
 * @package PayPalPlusPlugin\WC\PUI
 */
class PaymentInstructionData {

	/**
	 * Bank name.
	 *
	 * @var string
	 */
	private $bank_name;
	/**
	 * Bank account holer name.
	 *
	 * @var string
	 */
	private $account_holder_name;
	/**
	 * IBAN.
	 *
	 * @var string
	 */
	private $iban;
	/**
	 * Due date.
	 *
	 * @var string
	 */
	private $payment_due_date;
	/**
	 * Reference number.
	 *
	 * @var string
	 */
	private $reference_number;
	/**
	 * BIC.
	 *
	 * @var string
	 */
	private $bic;
	/**
	 * WooCommerce Order object.
	 *
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * PaymentInstructionData constructor.
	 *
	 * @param \WC_Order $order The WooCommcerce Order object.
	 */
	public function __construct( \WC_Order $order ) {

		$this->order = $order;

		$order_id = $this->get_order_id();

		$this->bank_name           = get_post_meta( $order_id, 'bank_name', true );
		$this->account_holder_name = get_post_meta( $order_id, 'account_holder_name', true );
		$this->iban                = get_post_meta( $order_id, 'international_bank_account_number', true );
		$this->payment_due_date    = get_post_meta( $order_id, 'payment_due_date', true );
		$this->reference_number    = get_post_meta( $order_id, 'reference_number', true );
		$this->bic                 = get_post_meta( $order_id, 'bank_identifier_code', true );

	}

	/**
	 * Returns the order id.
	 *
	 * @return int
	 */
	public function get_order_id() {

		return $this->order->get_id();
	}

	/**
	 * Checks if there are Payment Instructions present.
	 * Used to determine PUI Payments.
	 *
	 * @return bool
	 */
	public function has_payment_instructions() {

		return ! empty( $this->iban ) && ! empty( $this->bic );
	}

	/**
	 * Returns the bank name.
	 *
	 * @return string
	 */
	public function get_bank_name() {

		return $this->bank_name;
	}

	/**
	 * Get bank account holder name.
	 *
	 * @return string
	 */
	public function get_account_holder_name() {

		return $this->account_holder_name;
	}

	/**
	 * Returns the IBAN.
	 *
	 * @return string
	 */
	public function get_iban() {

		return $this->iban;
	}

	/**
	 * Returns the due date of the payment.
	 *
	 * @return string
	 */
	public function get_payment_due_date() {

		return date_i18n( get_option( 'date_format' ), strtotime( $this->payment_due_date ) );
	}

	/**
	 * Returns the BIC.
	 *
	 * @return string
	 */
	public function get_bic() {

		return $this->bic;
	}

	/**
	 * Returns the reference number.
	 *
	 * @return string
	 */
	public function get_reference_number() {

		return $this->reference_number;
	}
}