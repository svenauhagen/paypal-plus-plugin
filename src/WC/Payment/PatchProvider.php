<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 05.12.16
 * Time: 10:50
 */

namespace WCPayPalPlus\WC\Payment;

use Inpsyde\Lib\PayPal\Api\Patch;

/**
 * Class PatchProvider
 *
 * @package WCPayPalPlus\WC\Payment
 */
class PatchProvider {

	/**
	 * WooCommerce Order object.
	 *
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * PatchProvider constructor.
	 *
	 * @param \WC_Order $order      WooCommerce Order object.
	 * @param OrderData $order_data Order data provider.
	 */
	public function __construct( \WC_Order $order, OrderData $order_data = null ) {

		$this->order      = $order;
		$this->order_data = $order_data ?: new OrderData( $this->order );
	}

	/**
	 * Returns the invoice Patch.
	 *
	 * @param string $invoice_prefix The invoice prefix.
	 *
	 * @return Patch
	 */
	public function get_invoice_patch( $invoice_prefix ) {
		
		$invoice_number = preg_replace( '/[^a-zA-Z0-9]/', '', $this->order->get_order_number() );

		$invoice_patch = new Patch();
		$invoice_patch->setOp( 'add' )
		              ->setPath( '/transactions/0/invoice_number' )
		              ->setValue( $invoice_prefix . $invoice_number );

		return $invoice_patch;

	}

	/**
	 * Returns the custom Patch.
	 *
	 * @return Patch
	 */
	public function get_custom_patch() {

		$custom_patch = new Patch();
		$custom_patch->setOp( 'add' )
		             ->setPath( '/transactions/0/custom' )
		             ->setValue( wp_json_encode( [
			             'order_id'  => $this->order->get_id(),
			             'order_key' => $this->order->get_order_key(),
		             ] ) );

		return $custom_patch;

	}

	/**
	 * Returns the payment amount Patch.
	 *
	 * @return Patch
	 */
	public function get_payment_amount_patch() {

		$replace_patch = new Patch();

		$payment_data = [
			'total'    => $this->order_data->get_total(),
			'currency' => get_woocommerce_currency(),
			'details'  => [
				'subtotal' => $this->order_data->get_subtotal(),
				'shipping' => $this->order_data->get_total_shipping(),
			],
		];
		
		if ( $this->order_data->should_include_tax_in_total() ) {
			$payment_data['details']['tax'] = $this->order_data->get_total_tax();
		} else {
			$payment_data['details']['shipping'] += $this->order_data->get_shipping_tax();
		}

		$replace_patch->setOp( 'replace' )
		              ->setPath( '/transactions/0/amount' )
		              ->setValue( $payment_data );

		return $replace_patch;

	}

	/**
	 * Returns the billing Patch.
	 *
	 * @return Patch
	 */
	public function get_billing_patch() {

		$billing_data = ( $this->has_shipping_data() ) ? $this->get_shipping_address_data()
			: $this->get_billing_address_data();

		$billing_patch = new Patch();
		$billing_patch->setOp( 'add' )
		              ->setPath( '/transactions/0/item_list/shipping_address' )
		              ->setValue( $billing_data );

		return $billing_patch;
	}

	/**
	 * Checks if there is shipping address data.
	 *
	 * @return bool
	 */
	private function has_shipping_data() {

		return ! empty( $this->order->get_shipping_country() );

	}

	/**
	 * Returns the order's shipping address data.
	 *
	 * @return array
	 */
	private function get_shipping_address_data() {

		return [
			'recipient_name' => $this->order->get_shipping_first_name() . ' ' . $this->order->get_shipping_last_name(),
			'line1'          => $this->order->get_shipping_address_1(),
			'line2'          => $this->order->get_shipping_address_2(),
			'city'           => $this->order->get_shipping_city(),
			'state'          => $this->order->get_shipping_state(),
			'postal_code'    => $this->order->get_shipping_postcode(),
			'country_code'   => $this->order->get_shipping_country(),
		];
	}

	/**
	 * Returns the order's billing address data.
	 *
	 * @return array
	 */
	private function get_billing_address_data() {

		return [
			'recipient_name' => $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name(),
			'line1'          => $this->order->get_billing_address_1(),
			'line2'          => $this->order->get_billing_address_2(),
			'city'           => $this->order->get_billing_city(),
			'state'          => $this->order->get_billing_state(),
			'postal_code'    => $this->order->get_billing_postcode(),
			'country_code'   => $this->order->get_billing_country(),
		];
	}
}
