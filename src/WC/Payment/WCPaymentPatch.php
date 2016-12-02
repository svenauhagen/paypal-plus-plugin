<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 04.11.16
 * Time: 18:17
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payment;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class WCPaymentPatch {

	/**
	 * @var PaymentPatchData
	 */
	private $patch_data;

	/**
	 * WCPaymentPatch constructor.
	 *
	 * @param PaymentPatchData $patch_data
	 */
	public function __construct( PaymentPatchData $patch_data ) {

		$this->patch_data = $patch_data;
	}

	/**
	 * Creates a new Payment object
	 *
	 * @return Payment
	 */
	public function create() {

		$payment = new Payment();

		return $payment;
	}

	/**
	 * @return bool
	 */
	public function execute() {

		try {
			$payment = Payment::get( $this->patch_data->get_payment_id(), $this->patch_data->get_api_context() );
			$result  = $payment->update( $this->get_patch_request(), $this->patch_data->get_api_context() );
			if ( $result == TRUE ) {
				return TRUE;
			}
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'paypal-plus-plugin.log', 'payment_patch_exception', $ex );

			return FALSE;
		}

		return FALSE;
	}

	/**
	 * @return array
	 */
	private function get_patches() {

		$order        = $this->patch_data->get_order();
		$patchReplace = new Patch();

		$payment_data = [
			'total'    => $order
				->get_total(),
			'currency' => get_woocommerce_currency(),
			'details'  => [
				'subtotal' => $order->get_subtotal(),
				'shipping' => $order->get_total_shipping(),
				'tax'      => $order->get_total_tax(),
			],
		];

		$patchReplace->setOp( 'replace' )
		             ->setPath( '/transactions/0/amount' )
		             ->setValue( $payment_data );

		$invoice_number  = preg_replace( "/[^a-zA-Z0-9]/", "", $order->id );
		$patchAdd_custom = new Patch();
		$patchAdd_custom->setOp( 'add' )
		                ->setPath( '/transactions/0/custom' )
		                ->setValue( json_encode( [
			                'order_id'  => $order->id,
			                'order_key' => $order->order_key,
		                ] ) );
		$patches  = [ $patchReplace, $patchAdd_custom ];

		$patchAdd = new Patch();
		$patchAdd->setOp( 'add' )
		         ->setPath( '/transactions/0/invoice_number' )
		         ->setValue( $this->patch_data->get_invoice_prefix() . $invoice_number );
		$patches [] = $patchAdd;

		if ( ! empty( $order->shipping_country ) ) {

			$billing_data = [
				'recipient_name' => $order->shipping_first_name . ' ' . $order->shipping_last_name,
				'line1'          => $order->shipping_address_1,
				'line2'          => $order->shipping_address_2,
				'city'           => $order->shipping_city,
				'state'          => $order->shipping_state,
				'postal_code'    => $order->shipping_postcode,
				'country_code'   => $order->shipping_country,
			];

			$patchBilling = new Patch();
			$patchBilling->setOp( 'add' )
			             ->setPath( '/transactions/0/item_list/shipping_address' )
			             ->setValue( $billing_data );
			$patches [] = $patchBilling;
		}

		return $patches;
	}

	private function get_patch_request() {

		$patchRequest = new PatchRequest();

		$patchRequest->setPatches( $this->get_patches() );

		return $patchRequest;
	}
}