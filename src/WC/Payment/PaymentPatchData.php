<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 17:26
 */

namespace PayPalPlusPlugin\WC\Payment;

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
	 * @var PatchProvider
	 */
	private $patch_provider;

	/**
	 * PaymentPatchData constructor.
	 *
	 * @param \WC_Order     $order
	 * @param string        $payment_id
	 * @param string        $invoice_prefix
	 * @param ApiContext    $api_context
	 * @param PatchProvider $patch_provider
	 */
	public function __construct(
		\WC_Order $order,
		$payment_id,
		$invoice_prefix,
		ApiContext $api_context,
		PatchProvider $patch_provider = NULL
	) {

		$this->order          = $order;
		$this->payment_id     = $payment_id;
		$this->invoice_prefix = $invoice_prefix;
		$this->api_context    = $api_context;
		if ( ! is_null( $patch_provider ) ) {
			$this->patch_provider = $patch_provider;

		} else {
			$this->patch_provider = new PatchProvider( $this->order );
		}
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

		$patches = [
			$this->patch_provider->get_payment_amount_patch(),
			$this->patch_provider->get_custom_patch(),
			$this->patch_provider->get_invoice_patch( $this->get_invoice_prefix() ),
		];

		if ( $this->patch_provider->should_patch_billing() ) {

			$patches [] = $this->patch_provider->get_billing_patch();
		}

		return $patches;
	}
}