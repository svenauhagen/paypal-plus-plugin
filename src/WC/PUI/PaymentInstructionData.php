<?php
namespace PayPalPlusPlugin\WC\PUI;

/**
 * Created by PhpStorm.
 * User: biont
 * Date: 16.01.17
 * Time: 13:44
 */
class PaymentInstructionData {

	/**
	 * @var string
	 */
	private $bank_name;
	/**
	 * @var string
	 */
	private $account_holder_name;
	/**
	 * @var string
	 */
	private $iban;
	/**
	 * @var string
	 */
	private $payment_due_date;
	/**
	 * @var string
	 */
	private $reference_number;
	/**
	 * @var string
	 */
	private $bic;
	/**
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * PaymentInstructionData constructor.
	 *
	 * @param \WC_Order $order
	 */
	public function __construct( \WC_Order $order ) {

		$this->order               = $order;
		$this->bank_name           = get_post_meta( $order->id, 'bank_name', true );
		$this->account_holder_name = get_post_meta( $order->id, 'account_holder_name', true );
		$this->iban                = get_post_meta( $order->id, 'international_bank_account_number',
			true );
		$this->payment_due_date    = get_post_meta( $order->id, 'payment_due_date', true );
		$this->reference_number    = get_post_meta( $order->id, 'reference_number', true );
		$this->bic                 = get_post_meta( $order->id, 'bank_identifier_code', true );

	}

	public function has_payment_instructions() {

		return ! empty( $this->iban ) && ! empty( $this->bic );
	}

	public function get_order_id() {

		return $this->order->id;
	}

	/**
	 * @return mixed
	 */
	public function get_bank_name() {

		return $this->bank_name;
	}

	/**
	 * @return mixed
	 */
	public function get_account_holder_name() {

		return $this->account_holder_name;
	}

	/**
	 * @return mixed
	 */
	public function get_iban() {

		return $this->iban;
	}

	/**
	 * @return mixed
	 */
	public function get_payment_due_date() {

		return date_i18n( get_option( 'date_format' ), strtotime( $this->payment_due_date ) );
	}

	/**
	 * @return mixed
	 */
	public function get_bic() {

		return $this->bic;
	}

	/**
	 * @return mixed
	 */
	public function get_reference_number() {

		return $this->reference_number;
	}
}