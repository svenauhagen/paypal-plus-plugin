<?php

namespace WCPayPalPlus\WC\PUI;

/**
 * Class PaymentInstructionData
 *
 * @package WCPayPalPlus\WC\PUI
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
     * The legal note HTML
     *
     * @var string
     */
	private $legal_note;

    /**
     * PaymentInstructionData constructor.
     *
     * @param \WC_Order $order The WooCommcerce Order object.
     * @param \WC_Payment_Gateway $legal_note
     */
	public function __construct( \WC_Order $order, $legal_note ) {

		$this->order = $order;

		$this->bank_name           = $order->get_meta( 'bank_name' );
		$this->account_holder_name = $order->get_meta( 'account_holder_name' );
		$this->iban                = $order->get_meta( 'international_bank_account_number' );
		$this->payment_due_date    = $order->get_meta( 'payment_due_date' );
		$this->reference_number    = $order->get_meta( 'reference_number' );
		$this->bic                 = $order->get_meta( 'bank_identifier_code' );
        $this->legal_note          = $legal_note;
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

    /**
	 * Returns the legal note.
	 *
	 * @return string
	 */
	public function get_legal_note() {

		return wpautop( wptexturize( $this->legal_note ) );
	}
}