<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 10:01
 */

namespace PayPalPlusPlugin\WC\IPN;

class OrderUpdater {

	/**
	 * @var \WC_Order
	 */
	private $order;
	/**
	 * @var IPNData
	 */
	private $data;
	/**
	 * @var PaymentValidator
	 */
	private $validator;

	/**
	 * OrderUpdater constructor.
	 *
	 * @param \WC_Order        $order
	 * @param IPNData          $data
	 * @param PaymentValidator $validator
	 */
	public function __construct(
		\WC_Order $order,
		IPNData $data,
		PaymentValidator $validator = NULL
	) {

		$this->order     = $order;
		$this->data      = $data;
		$this->validator = $validator ?: new PaymentValidator( $order );
	}

	/**
	 * Handle a completed payment.
	 *
	 */
	public function payment_status_completed() {

		if ( $this->order->has_status( 'completed' ) ) {
			exit;
		}

		if ( ! $this->validator->validate_transaction_type( $this->data->get( 'txn_type' ) )
		     || $this->validator->validate_currency( $this->data->get( 'mc_currency' ) )
		     || $this->validator->validate_amount( $this->data->get( 'mc_gross' ) )
		) {
			$this->order->update_status( 'on-hold', $this->validator->get_last_error() );

			return;
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
			$this->payment_on_hold( $this->order,
				sprintf( __( 'Payment pending: %s', 'woo-paypal-plus' ), $this->data->get( 'pending_reason' ) ) );
		}
	}

	/**
	 * Handle a pending payment.
	 *
	 */
	public function payment_status_pending() {

		$this->payment_status_completed();
	}

	/**
	 * Handle a failed payment.
	 *
	 * @param array $posted
	 */
	public function payment_status_failed( $posted ) {

		$this->order->update_status( 'failed',
			sprintf( __( 'Payment %s via IPN.', 'woo-paypal-plus' ), wc_clean( $posted['payment_status'] ) ) );
	}

	/**
	 * Handle a denied payment.
	 *
	 * @param array $posted
	 */
	public function payment_status_denied( $posted ) {

		$this->payment_status_failed( $posted );
	}

	/**
	 * Handle an expired payment.
	 *
	 * @param array $posted
	 */
	public function payment_status_expired( $posted ) {

		$this->payment_status_failed( $posted );
	}

	/**
	 * Handle a voided payment.
	 *
	 * @param array $posted
	 */
	public function payment_status_voided( $posted ) {

		$this->payment_status_failed( $posted );
	}

	/**
	 * Handle a refunded order.
	 *
	 * @param array $posted
	 */
	public function payment_status_refunded( $posted ) {

		if ( $this->order->get_total() == ( $posted['mc_gross'] * - 1 ) ) {
			$this->order->update_status( 'refunded',
				sprintf( __( 'Payment %s via IPN.', 'woo-paypal-plus' ), strtolower( $posted['payment_status'] ) ) );
			$this->send_ipn_email_notification(
				sprintf( __( 'Payment for order %s refunded', 'woo-paypal-plus' ),
					'<a class="link" href="' . esc_url( admin_url( 'post.php?post=' . $this->order->id . '&action=edit' ) ) . '">' . $this->order->get_order_number() . '</a>' ),
				sprintf( __( 'Order #%s has been marked as refunded - PayPal reason code: %s', 'woo-paypal-plus' ),
					$this->order->get_order_number(), $posted['reason_code'] )
			);
		}
	}

	/**
	 * Handle a reveral.
	 *
	 * @param array $posted
	 */
	public function payment_status_reversed( $posted ) {

		$this->order->update_status( 'on-hold',
			sprintf( __( 'Payment %s via IPN.', 'woo-paypal-plus' ), wc_clean( $posted['payment_status'] ) ) );
		$this->send_ipn_email_notification(
			sprintf( __( 'Payment for order %s reversed', 'woo-paypal-plus' ),
				'<a class="link" href="' . esc_url( admin_url( 'post.php?post=' . $this->order->id . '&action=edit' ) ) . '">' . $this->order->get_order_number() . '</a>' ),
			sprintf( __( 'Order #%s has been marked on-hold due to a reversal - PayPal reason code: %s',
				'woo-paypal-plus' ), $this->order->get_order_number(), wc_clean( $posted['reason_code'] ) )
		);
	}

	/**
	 * Handle a cancelled reveral.
	 *
	 * @param array $posted
	 */
	public function payment_status_canceled_reversal( $posted ) {

		$this->send_ipn_email_notification(
			sprintf( __( 'Reversal cancelled for order #%s', 'woo-paypal-plus' ), $this->order->get_order_number() ),
			sprintf( __( 'Order #%s has had a reversal cancelled. Please check the status of payment and update the order status accordingly here: %s',
				'woo-paypal-plus' ), $this->order->get_order_number(),
				esc_url( admin_url( 'post.php?post=' . $this->order->id . '&action=edit' ) ) )
		);
	}

	/**
	 * Complete order, add transaction ID and note.
	 *
	 * @param  string $transaction_id
	 * @param  string $note
	 */
	private function payment_complete( $transaction_id = '', $note = '' ) {

		$this->order->add_order_note( $note );
		$this->order->payment_complete( $transaction_id );
	}

	/**
	 * Hold order and add note.
	 *
	 * @param  \WC_Order $order
	 * @param  string    $reason
	 */
	private function payment_on_hold( $order, $reason = '' ) {

		$order->update_status( 'on-hold', $reason );
		$order->reduce_order_stock();
		WC()->cart->empty_cart();
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

}