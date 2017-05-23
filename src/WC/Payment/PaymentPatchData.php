<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 17:26
 */

namespace WCPayPalPlus\WC\Payment;

use Inpsyde\Lib\PayPal\Api\Patch;
use Inpsyde\Lib\PayPal\Api\PatchRequest;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

/**
 * Class PaymentPatchData
 *
 * @package WCPayPalPlus\WC\Payment
 */
class PaymentPatchData {

	/**
	 * WooCommerce Order object.
	 *
	 * @var \WC_Order
	 */
	private $order;
	/**
	 * The Payment ID.
	 *
	 * @var string
	 */
	private $payment_id;
	/**
	 * The invoice prefix.
	 *
	 * @var string
	 */
	private $invoice_prefix;
	/**
	 * The PayPal SDK ApiContext object.
	 *
	 * @var ApiContext
	 */
	private $api_context;
	/**
	 * The PatchProvider object
	 *
	 * @var PatchProvider
	 */
	private $patch_provider;

	/**
	 * PaymentPatchData constructor.
	 *
	 * @param \WC_Order     $order          WooCommerce Order object.
	 * @param string        $payment_id     The Payment ID.
	 * @param string        $invoice_prefix The invoice prefix.
	 * @param ApiContext    $api_context    The PayPal SDK ApiContext object.
	 * @param PatchProvider $patch_provider The PatchProvider object.
	 */
	public function __construct(
		\WC_Order $order,
		$payment_id,
		$invoice_prefix,
		ApiContext $api_context,
		PatchProvider $patch_provider = null
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
	 * Returns the WooCommerce Order object
	 *
	 * @return \WC_Order
	 */
	public function get_order() {

		return $this->order;
	}

	/**
	 * Fetches an existing Payment object via API call
	 *
	 * @return Payment
	 */
	public function get_payment() {

		return Payment::get( $this->get_payment_id(), $this->get_api_context() );

	}

	/**
	 * Returns the payment ID.
	 *
	 * @return string
	 */
	public function get_payment_id() {

		return $this->payment_id;
	}

	/**
	 * Returns the APIContext object.
	 *
	 * @return ApiContext
	 */
	public function get_api_context() {

		return $this->api_context;
	}

	/**
	 * Returns a configured PatchRequest object.
	 *
	 * @return PatchRequest
	 */
	public function get_patch_request() {

		$patch_request = new PatchRequest();

		$patch_request->setPatches( $this->get_patches() );

		return $patch_request;
	}

	/**
	 * Returns an array of configured Patch objects relevant to the current request
	 *
	 * @return Patch[]
	 */
	private function get_patches() {

		$patches = [
			$this->patch_provider->get_payment_amount_patch(),
			$this->patch_provider->get_custom_patch(),
			$this->patch_provider->get_invoice_patch( $this->get_invoice_prefix() ),
			$this->patch_provider->get_billing_patch(),
		];

		return $patches;
	}

	/**
	 * Returns the invoice prefix.
	 *
	 * @return string
	 */
	public function get_invoice_prefix() {

		return $this->invoice_prefix;
	}
}
