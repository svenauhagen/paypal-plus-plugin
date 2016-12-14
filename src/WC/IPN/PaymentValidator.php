<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 10:24
 */

namespace PayPalPlusPlugin\WC\IPN;

/**
 * Class PaymentValidator
 *
 * @package PayPalPlusPlugin\WC\IPN
 */
class PaymentValidator {

	/**
	 * The last error that occurred during validation
	 *
	 * @var string
	 */
	private $last_error;
	/**
	 * @var array|NULL
	 */
	private $accepted_transaction_types;
	/**
	 * @var \WC_Order
	 */
	private $order;
	private $transaction_type;
	private $currency;
	private $amount;

	/**
	 * PaymentValidator constructor.
	 *
	 * @param           $transaction_type
	 * @param           $currency
	 * @param           $amount
	 * @param \WC_Order $order                      The WooCommerce order.
	 * @param array     $accepted_transaction_types An array of accepted transaction types.
	 */
	public function __construct(
		$transaction_type,
		$currency,
		$amount,
		\WC_Order $order, array $accepted_transaction_types = NULL
	) {

		$this->order = $order;

		$this->accepted_transaction_types = $accepted_transaction_types
			?: [
				'cart',
				'instant',
				'express_checkout',
				'web_accept',
				'masspay',
				'send_money',
			];
		$this->transaction_type           = $transaction_type;
		$this->currency                   = $currency;
		$this->amount                     = $amount;
		$this->accepted_transaction_types = $accepted_transaction_types;
	}

	/**
	 * Runs all validation method
	 *
	 * @return bool
	 */
	public function is_valid() {

		return ( $this->validate_transaction_type( $this->transaction_type )
		         && $this->validate_currency( $this->currency )
		         && $this->validate_amount( $this->amount ) );
	}

	/**
	 * Returns the last validation error
	 *
	 * @return string
	 */
	public function get_last_error() {

		return $this->last_error;
	}

	/**
	 * Check for a valid transaction type.
	 *
	 * @param string $transaction_type The transaction type to test against.
	 *
	 * @return bool
	 */
	private function validate_transaction_type( $transaction_type ) {

		if ( ! in_array( strtolower( $transaction_type ), $this->accepted_transaction_types, TRUE ) ) {
			$this->last_error = sprintf( __( 'Validation error: Invalid transaction type "%s".',
				'woo-paypal-plus' ),
				$transaction_type );

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Check currency from IPN matches the order.
	 *
	 * @param string $currency The currency to test against.
	 *
	 * @return bool
	 */
	private function validate_currency( $currency ) {

		if ( $this->order->get_order_currency() !== $currency ) {
			$this->last_error = sprintf( __( 'Validation error: PayPal currencies do not match (code %s).',
				'woo-paypal-plus' ),
				$currency );

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Check payment amount from IPN matches the order.
	 *
	 * @param int $amount
	 *
	 * @return bool
	 */
	private function validate_amount( $amount ) {

		if ( number_format( $this->order->get_total(), 2, '.', '' ) !== number_format( $amount, 2, '.', '' ) ) {
			$this->last_error = sprintf( __( 'Validation error: PayPal amounts do not match (gross %s).',
				'woo-paypal-plus' ),
				$amount );

			return FALSE;
		}

		return TRUE;
	}

}