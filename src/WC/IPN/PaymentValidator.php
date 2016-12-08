<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 10:24
 */

namespace PayPalPlusPlugin\WC\IPN;

class PaymentValidator {

	private $last_error;
	/**
	 * @var array|NULL
	 */
	private $accepted_transaction_types;
	/**
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * PaymentValidator constructor.
	 *
	 * @param \WC_Order $order
	 * @param array     $accepted_transaction_types
	 */
	public function __construct( \WC_Order $order, array $accepted_transaction_types = NULL ) {

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
	}

	/**
	 * Check for a valid transaction type.
	 *
	 * @param string $transaction_type
	 *
	 * @return bool
	 */
	public function validate_transaction_type( $transaction_type ) {

		if ( ! in_array( strtolower( $transaction_type ), $this->accepted_transaction_types ) ) {
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
	 * @param string $currency
	 *
	 * @return bool
	 */
	public function validate_currency( $currency ) {

		if ( $this->order->get_order_currency() != $currency ) {
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
	public function validate_amount( $amount ) {

		if ( number_format( $this->order->get_total(), 2, '.', '' ) != number_format( $amount, 2, '.', '' ) ) {
			$this->last_error = sprintf( __( 'Validation error: PayPal amounts do not match (gross %s).',
				'woo-paypal-plus' ),
				$amount );

			return FALSE;
		}

		return TRUE;
	}

	public function get_last_error() {

		return $this->last_error;
	}

}