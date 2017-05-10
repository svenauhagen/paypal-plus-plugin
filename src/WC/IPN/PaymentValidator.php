<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 10:24
 */

namespace WCPayPalPlus\WC\IPN;

/**
 * Class PaymentValidator
 *
 * @package WCPayPalPlus\WC\IPN
 */
class PaymentValidator {

	/**
	 * The last error that occurred during validation
	 *
	 * @var string
	 */
	private $last_error;
	/**
	 * The transaction types to validate against
	 *
	 * @var array|NULL
	 */
	private $accepted_transaction_types;
	/**
	 * WooCommerce Order object
	 *
	 * @var \WC_Order
	 */
	private $order;
	/**
	 * The actual transaction type of the PayPal Payment
	 *
	 * @var string
	 */
	private $transaction_type;
	/**
	 * The currency used by the PayPal Payment
	 *
	 * @var string
	 */
	private $currency;
	/**
	 * The payment amount.
	 *
	 * @var float
	 */
	private $amount;

	/**
	 * PaymentValidator constructor.
	 *
	 * @param string    $transaction_type           The transaction type.
	 * @param string    $currency                   The currency used.
	 * @param float     $amount                     The payment amount.
	 * @param \WC_Order $order                      The WooCommerce order.
	 * @param array     $accepted_transaction_types Optional. An array of accepted transaction types.
	 */
	public function __construct(
		$transaction_type,
		$currency,
		$amount,
		\WC_Order $order,
		array $accepted_transaction_types = null
	) {

		$this->transaction_type           = $transaction_type;
		$this->currency                   = $currency;
		$this->amount                     = $amount;
		$this->order                      = $order;
		$this->accepted_transaction_types = $accepted_transaction_types
			?: [
				'cart',
				'instant',
				'express_checkout',
				'web_accept',
				'masspay',
				'send_money',
			];
	}

	/**
	 * Runs all validation method
	 *
	 * @return bool
	 */
	public function is_valid_payment() {

		return ( $this->validate_transaction_type( $this->transaction_type )
		         && $this->validate_currency( $this->currency )
		         && $this->validate_payment_amount( $this->amount ) );
	}

	/**
	 * Check for a valid transaction type.
	 *
	 * @param string $transaction_type The transaction type to test against.
	 *
	 * @return bool
	 */
	private function validate_transaction_type( $transaction_type ) {

		if ( ! in_array( strtolower( $transaction_type ), $this->accepted_transaction_types, true ) ) {
			$this->last_error = sprintf(
				__(
					'Validation error: Invalid transaction type "%s".',
					'woo-paypalplus'
				),
				$transaction_type
			);

			return false;
		}

		return true;
	}

	/**
	 * Check currency from IPN matches the order.
	 *
	 * @param string $currency The currency to test against.
	 *
	 * @return bool
	 */
	private function validate_currency( $currency ) {

		$wc_currency = $this->order->get_currency();
		if ( $wc_currency !== $currency ) {
			$this->last_error = sprintf(
				__(
					'Validation error: PayPal currencies do not match (PayPal: %1$1s, WooCommerce: %2$2s).',
					'woo-paypalplus'
				),
				$currency,
				$wc_currency
			);

			return false;
		}

		return true;
	}

	/**
	 * Check payment amount from IPN matches the order.
	 *
	 * @param int $amount The payment amount.
	 *
	 * @return bool
	 */
	private function validate_payment_amount( $amount ) {

		$wc_total = number_format( $this->order->get_total(), 2, '.', '' );
		$pp_total = number_format( $amount, 2, '.', '' );
		if ( $pp_total !== $wc_total ) {
			$this->last_error = sprintf(
				__(
					'Validation error: PayPal payment amounts do not match (gross %1$1s, should be %2$2s).',
					'woo-paypalplus'
				),
				$amount,
				$wc_total
			);

			return false;
		}

		return true;
	}

	/**
	 * Checks if we're dealing with a valid refund request.
	 *
	 * @return bool
	 */
	public function is_valid_refund() {

		$wc_total = number_format( $this->sanitize_string_amount( $this->order->get_total() ), 2, '.', '' );
		$pp_total = number_format( $this->sanitize_string_amount( $this->amount ) * - 1, 2, '.', '' );

		return ( $pp_total === $wc_total );
	}

	private function sanitize_string_amount( $amt ) {

		if ( is_string( $amt ) ) {
			$amt = str_replace( ',', '.', $amt );
		}

		return $amt;

	}

	/**
	 * Returns the last validation error
	 *
	 * @return string
	 */
	public function get_last_error() {

		return $this->last_error;
	}

}
