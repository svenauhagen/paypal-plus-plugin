<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 10:24
 */

namespace PayPalPlusPlugin\WC\IPN;

class PaymentValidator {

	/**
	 * Check for a valid transaction type.
	 *
	 * @param string $transaction_type
	 *
	 * @return bool
	 */
	public function validate_transaction_type( $transaction_type ) {

		$accepted_types = [ 'cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money' ];
		if ( ! in_array( strtolower( $transaction_type ), $accepted_types ) ) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Check currency from IPN matches the order.
	 *
	 * @param \WC_Order $order
	 * @param string    $currency
	 *
	 * @return bool
	 */
	public function validate_currency( $order, $currency ) {

		if ( $order->get_order_currency() != $currency ) {
			$order->update_status( 'on-hold',
				sprintf( __( 'Validation error: PayPal currencies do not match (code %s).', 'woo-paypal-plus' ),
					$currency ) );

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Check payment amount from IPN matches the order.
	 *
	 * @param \WC_Order $order
	 * @param int       $amount
	 *
	 * @return bool
	 */
	public function validate_amount( $order, $amount ) {

		if ( number_format( $order->get_total(), 2, '.', '' ) != number_format( $amount, 2, '.', '' ) ) {
			$order->update_status( 'on-hold',
				sprintf( __( 'Validation error: PayPal amounts do not match (gross %s).', 'woo-paypal-plus' ),
					$amount ) );

			return FALSE;
		}

		return TRUE;
	}

}