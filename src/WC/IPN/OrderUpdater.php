<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 10:01
 */

namespace PayPalPlusPlugin\WC\IPN;

/**
 * Class OrderUpdater
 *
 * @package PayPalPlusPlugin\WC\IPN
 */
class OrderUpdater {

	/**
	 * WooComcerce Order object
	 *
	 * @var \WC_Order
	 */
	private $order;
	/**
	 * Request Data
	 *
	 * @var IPNData
	 */
	private $data;
	/**
	 * Payment Validation handler
	 *
	 * @var PaymentValidator
	 */
	private $validator;

	/**
	 * OrderUpdater constructor.
	 *
	 * @param \WC_Order        $order     WooCommerce Order.
	 * @param IPNData          $data      IPN Data.
	 * @param PaymentValidator $validator Payment validator.
	 */
	public function __construct(
		\WC_Order $order,
		IPNData $data,
		PaymentValidator $validator = null
	) {

		$this->order     = $order;
		$this->data      = $data;
		$this->validator = $validator
			?: new PaymentValidator(
				$this->data->get( 'txn_type' ),
				$this->data->get( 'mc_currency' ),
				$this->data->get( 'mc_gross' ),
				$order
			);
	}

	/**
	 * Handle a pending payment.
	 *
	 * @return bool
	 */
	public function payment_status_pending() {

		return $this->payment_status_completed();
	}

	/**
	 * Handle a completed payment.
	 *
	 * @return bool
	 */
	public function payment_status_completed() {

		if ( $this->order->has_status( 'completed' ) ) {
			return true;
		}

		if ( ! $this->validator->is_valid() ) {
			$this->order->update_status( 'on-hold', $this->validator->get_last_error() );

			return false;
		}

		$this->save_paypal_meta_data();

		if ( 'completed' === $this->data->get( 'payment_status' ) ) {

			$transaction_id = wc_clean( $this->data->get( 'txn_id' ) );
			$note           = __( 'IPN payment completed', 'woo-paypal-plus' );

			$this->payment_complete( $transaction_id, $note );

			if ( ! empty( $fee = $this->data->get( 'mc_fee' ) ) ) {
				update_post_meta( $this->order->id, 'PayPal Transaction Fee', wc_clean( $fee ) );
			}
		} else {
			$this->payment_on_hold(
				sprintf(
					__( 'Payment pending: %s', 'woo-paypal-plus' ),
					$this->data->get( 'pending_reason' )
				)
			);
		}

		return true;
	}

	/**
	 * Save relevant data from the IPN to the order.
	 */
	private function save_paypal_meta_data() {

		foreach (
			[
				'payer_email',
				'first_name',
				'last_name',
				'payment_type',
			]
			as $key
		) {
			if ( ! empty( $value = $this->data->get( $key ) ) ) {
				update_post_meta( $this->order->id, 'Payer PayPal address', wc_clean( $value ) );
			}
		}

	}

	/**
	 * Complete order, add transaction ID and note.
	 *
	 * @param  string $transaction_id The Transaction ID.
	 * @param  string $note           Payment note.
	 */
	private function payment_complete( $transaction_id = '', $note = '' ) {

		$this->order->add_order_note( $note );
		$this->order->payment_complete( $transaction_id );
	}

	/**
	 * Hold order and add note.
	 *
	 * @param  string $reason Reason for refunding.
	 */
	private function payment_on_hold( $reason = '' ) {

		$this->order->update_status( 'on-hold', $reason );
		$this->order->reduce_order_stock();
		WC()->cart->empty_cart();
	}

	/**
	 * Handle a denied payment.
	 *
	 * @return bool
	 */
	public function payment_status_denied() {

		return $this->payment_status_failed();
	}

	/**
	 * Handle a failed payment.
	 *
	 * @return bool
	 */
	public function payment_status_failed() {

		return $this->order->update_status( 'failed',
			sprintf(
				__( 'Payment %s via IPN.', 'woo-paypal-plus' ),
				wc_clean( $this->data->get( 'payment_status' ) )
			)
		);
	}

	/**
	 * Handle an expired payment.
	 *
	 * @return bool
	 */
	public function payment_status_expired() {

		return $this->payment_status_failed();
	}

	/**
	 * Handle a voided payment.
	 *
	 * @return bool
	 */
	public function payment_status_voided() {

		return $this->payment_status_failed();
	}

	/**
	 * Handle a refunded order.
	 */
	public function payment_status_refunded() {

		if ( $this->order->get_total() === ( $this->data->get( 'mc_gross', 0 ) * - 1 ) ) {
			$this->order->update_status(
				'refunded',
				sprintf( __( 'Payment %s via IPN.', 'woo-paypal-plus' ),
					strtolower( $this->data->get( 'payment_status' ) )
				)
			);
			do_action( 'paypal_plus_plugin_ipn_payment_update', 'refunded', $this->data );
		}
	}

	/**
	 * Handle a payment reversal.
	 */
	public function payment_status_reversed() {

		$this->order->update_status( 'on-hold',
			sprintf(
				__( 'Payment %s via IPN.', 'woo-paypal-plus' ),
				wc_clean(
					$this->data->get( 'payment_status' )
				)
			)
		);

		do_action( 'paypal_plus_plugin_ipn_payment_update', 'reversed', $this->data );

	}

	/**
	 * Handle a cancelled reversal.
	 */
	public function payment_status_canceled_reversal() {

		do_action( 'paypal_plus_plugin_ipn_payment_update', 'canceled_reversal', $this->data );

	}

}
