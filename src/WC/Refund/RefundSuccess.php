<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 10:54
 */

namespace PayPalPlusPlugin\WC\Refund;

use PayPalPlusPlugin\WC\RequestSuccessHandler;

class RefundSuccess implements RequestSuccessHandler {

	/**
	 * @var \WC_Order
	 */
	private $order;
	/**
	 * @var
	 */
	private $transaction_id;

	/**
	 * RefundSuccess constructor.
	 *
	 * @param \WC_Order $order
	 * @param           $transaction_id
	 * @param           $reason
	 */
	public function __construct( \WC_Order $order, $transaction_id, $reason ) {

		$this->order          = $order;
		$this->transaction_id = $transaction_id;
	}

	/**
	 * @return bool
	 */
	public function execute() {

		$this->order->add_order_note( 'Refund Transaction ID:' . $this->transaction_id );
		if ( isset( $this->reason ) && ! empty( $this->reason ) ) {
			$this->order->add_order_note( 'Reason for Refund :' . $this->reason );
		}
		$max_remaining_refund = wc_format_decimal( $this->order->get_total() - $this->order->get_total_refunded() );
		if ( ! $max_remaining_refund > 0 ) {
			$this->order->update_status( 'refunded' );
		}

		return TRUE;
	}
}