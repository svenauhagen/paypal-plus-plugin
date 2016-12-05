<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 05.12.16
 * Time: 10:50
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Patch;

class PatchProvider {

	/**
	 * @var \WC_Order
	 */
	private $order;

	public function __construct( \WC_Order $order ) {

		$this->order = $order;
	}

	/**
	 * @param string $invoice_prefix
	 *
	 * @return Patch
	 */
	public function get_invoice_patch( $invoice_prefix ) {

		$invoice_number = preg_replace( "/[^a-zA-Z0-9]/", "", $this->order->id );

		$patchAdd = new Patch();
		$patchAdd->setOp( 'add' )
		         ->setPath( '/transactions/0/invoice_number' )
		         ->setValue( $invoice_prefix . $invoice_number );

		return $patchAdd;

	}

	public function get_custom_patch() {

		$patchAdd_custom = new Patch();
		$patchAdd_custom->setOp( 'add' )
		                ->setPath( '/transactions/0/custom' )
		                ->setValue( json_encode( [
			                'order_id'  => $this->order->id,
			                'order_key' => $this->order->order_key,
		                ] ) );

		return $patchAdd_custom;

	}

	public function get_payment_amount_patch() {

		$patchReplace = new Patch();

		$payment_data = [
			'total'    => $this->order
				->get_total(),
			'currency' => get_woocommerce_currency(),
			'details'  => [
				'subtotal' => $this->order->get_subtotal(),
				'shipping' => $this->order->get_total_shipping(),
				'tax'      => $this->order->get_total_tax(),
			],
		];

		$patchReplace->setOp( 'replace' )
		             ->setPath( '/transactions/0/amount' )
		             ->setValue( $payment_data );

		return $patchReplace;

	}

	public function get_billing_patch() {

		$billing_data = [
			'recipient_name' => $this->order->shipping_first_name . ' ' . $this->order->shipping_last_name,
			'line1'          => $this->order->shipping_address_1,
			'line2'          => $this->order->shipping_address_2,
			'city'           => $this->order->shipping_city,
			'state'          => $this->order->shipping_state,
			'postal_code'    => $this->order->shipping_postcode,
			'country_code'   => $this->order->shipping_country,
		];

		$patchBilling = new Patch();
		$patchBilling->setOp( 'add' )
		             ->setPath( '/transactions/0/item_list/shipping_address' )
		             ->setValue( $billing_data );

		return $patchBilling;
	}

	/**
	 * @return bool
	 */
	public function should_patch_billing() {

		return ! empty( $this->order->shipping_country );

	}
}