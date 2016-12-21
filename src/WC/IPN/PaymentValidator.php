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
	public function is_valid() {

		return ( $this->validate_transaction_type( $this->transaction_type )
		         && $this->validate_currency( $this->currency )
		         && $this->validate_amount( $this->amount ) );
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
					'woo-paypal-plus'
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

		if ( $wc_currency = $this->order->get_order_currency() !== $currency ) {
			$this->last_error = sprintf(
				__(
					'Validation error: PayPal currencies do not match (PayPal: %1$1s, WooCommerce: %2$2s).',
					'woo-paypal-plus'
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
	private function validate_amount( $amount ) {

		if ( number_format( $this->order->get_total(), 2, '.', '' ) !== number_format( $amount, 2, '.', '' ) ) {
			$this->last_error = sprintf(
				__(
					'Validation error: PayPal amounts do not match (gross %s).',
					'woo-paypal-plus'
				),
				$amount
			);

			return false;
		}

		return true;
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
