<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 17:26
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payment;
use PayPal\Rest\ApiContext;

class PaymentPatchData {

	/**
	 * @var \WC_Order
	 */
	private $order;
	/**
	 * @var string
	 */
	private $payment_id;
	/**
	 * @var string
	 */
	private $invoice_prefix;
	/**
	 * @var ApiContext
	 */
	private $api_context;

	/**
	 * PaymentPatchData constructor.
	 *
	 * @param \WC_Order  $order
	 * @param string     $payment_id
	 * @param string     $invoice_prefix
	 * @param ApiContext $api_context
	 */
	public function __construct(
		\WC_Order $order,
		$payment_id,
		$invoice_prefix,
		ApiContext $api_context
	) {

		$this->order          = $order;
		$this->payment_id     = $payment_id;
		$this->invoice_prefix = $invoice_prefix;
		$this->api_context    = $api_context;
	}

	/**
	 * @return \WC_Order
	 */
	public function get_order() {

		return $this->order;
	}

	/**
	 * @return string
	 */
	public function get_payment_id() {

		return $this->payment_id;
	}

	/**
	 * @return string
	 */
	public function get_invoice_prefix() {

		return $this->invoice_prefix;
	}

	/**
	 * @return ApiContext
	 */
	public function get_api_context() {

		return $this->api_context;
	}

	/**
	 * @return Payment
	 */
	public function get_payment() {

		return Payment::get( $this->get_payment_id(), $this->get_api_context() );

	}

	/**
	 * @return PatchRequest
	 */
	public function get_patch_request() {

		$patchRequest = new PatchRequest();

		$patchRequest->setPatches( $this->get_patches() );

		return $patchRequest;
	}

	/**
	 * @return array
	 */
	private function get_patches() {

		$order        = $this->get_order();
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
		$patches = [ $patchReplace, $patchAdd_custom ];

		$patchAdd = new Patch();
		$patchAdd->setOp( 'add' )
		         ->setPath( '/transactions/0/invoice_number' )
		         ->setValue( $this->get_invoice_prefix() . $invoice_number );
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
}