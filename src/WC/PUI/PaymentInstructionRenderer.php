<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 16.01.17
 * Time: 15:22
 */

namespace PayPalPlusPlugin\WC\PUI;

/**
 * Class PaymentInstructionRenderer
 *
 * @package PayPalPlusPlugin\WC\PUI
 */
class PaymentInstructionRenderer {

	/**
	 * @var PaymentInstructionView
	 */
	private $view;
	/**
	 * @var PaymentInstructionData
	 */
	private $data;

	public function register() {

		add_action( 'woocommerce_thankyou_paypal_plus', [ $this, 'delegate_thankyou' ], 10, 1 );
		add_action( 'woocommerce_email_before_order_table', [ $this, 'delegate_email' ], 10, 3 );
	}

	public function delegate_thankyou() {

		$order_key = filter_input( INPUT_GET, 'key' );

		$order    = wc_get_order( wc_get_order_id_by_order_key( $order_key ) );
		$pui_data = new PaymentInstructionData( $order );
		if ( ! $pui_data->has_payment_instructions() ) {
			return;
		}
		$pui_view = new PaymentInstructionView( $pui_data );
		$pui_view->thankyou_page();

	}

	public function delegate_email( $order, $sent_to_admin, $plain_text = false ) {

		$pui_data = new PaymentInstructionData( $order );
		if ( ! $pui_data->has_payment_instructions() ) {
			return;
		}
		$pui_view = new PaymentInstructionView( $pui_data );
		$pui_view->email_instructions();
	}

}