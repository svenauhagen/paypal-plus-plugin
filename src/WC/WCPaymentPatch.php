<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 04.11.16
 * Time: 18:17
 */

namespace PayPalPlusPlugin\WC;

use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payment;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class WCPaymentPatch extends WCPayPalPayment {

	/**
	 * @var \WC_Order
	 */
	private $order;
	/**
	 * @var ApiContext
	 */
	private $context;

	public function __construct( \WC_Order $order, ApiContext $context, array $config ) {

		$this->order = $order;
		parent::__construct( $config, $order );
		$this->context = $context;
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

	public function execute() {

		try {
			$payment = Payment::get( $this->config['payment_id'], $this->context );
			$result = $payment->update( $this->get_patch_request(), $this->context );
			if ( $result == TRUE ) {
				return TRUE;
			}
		} catch ( PayPalConnectionException $ex ) {
			error_log( $ex->getMessage() );
			error_log( $ex->getData() );

			return FALSE;
		} catch ( \Exception $ex ) {
			error_log( $ex->getMessage() );

			return FALSE;
		}
	}

	private function get_patches() {

		$patchReplace = new Patch();

		$payment_data = [
			'total'    => $this->order->get_total(),
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

		$invoice_number  = preg_replace( "/[^a-zA-Z0-9]/", "", $this->order->id );
		$patchAdd_custom = new Patch();
		$patchAdd_custom->setOp( 'add' )
		                ->setPath( '/transactions/0/custom' )
		                ->setValue( json_encode( [
			                'order_id'  => $this->order->id,
			                'order_key' => $this->order->order_key,
		                ] ) );
		$patches = [ $patchReplace, $patchAdd_custom ];
		if ( ! empty( $this->order->shipping_country ) ) {

			$billing_data = [
				'recipient_name' => $this->order->shipping_first_name . ' ' . $this->order->shipping_last_name,
				'line1'          => $this->order->shipping_address_1,
				'line2'          => $this->order->shipping_address_2,
				'city'           => $this->order->shipping_city,
				'state'          => $this->order->shipping_state,
				'postal_code'    => $this->order->shipping_postcode,
				'country_code'   => $this->order->shipping_country,
			];

			$patchAdd = new Patch();
			$patchAdd->setOp( 'add' )
			         ->setPath( '/transactions/0/item_list/shipping_address' )
			         ->setValue( $billing_data );
			$patchAddone = new Patch();
			$patchAddone->setOp( 'add' )
			            ->setPath( '/transactions/0/invoice_number' )
			            ->setValue( $this->config['invoice_prefix'] . $invoice_number );
			$patches [] = $patchAdd;
			$patches [] = $patchAddone;

		} else {
			$patchAdd = new Patch();
			$patchAdd->setOp( 'add' )
			         ->setPath( '/transactions/0/invoice_number' )
			         ->setValue( $this->config['invoice_prefix'] . $invoice_number );
			$patches [] = $patchAdd;
		}

		return $patches;
	}

	private function get_patch_request() {

		$patchRequest = new PatchRequest();

		$patchRequest->setPatches( $this->get_patches() );

		return $patchRequest;
	}
}