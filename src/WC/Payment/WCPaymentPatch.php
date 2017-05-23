<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 04.11.16
 * Time: 18:17
 */

namespace WCPayPalPlus\WC\Payment;

use Inpsyde\Lib\PayPal\Api\Patch;
use Inpsyde\Lib\PayPal\Api\PatchRequest;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

/**
 * Class WCPaymentPatch
 *
 * @package WCPayPalPlus\WC\Payment
 */
class WCPaymentPatch {

	/**
	 * Patch data object.
	 *
	 * @var PaymentPatchData
	 */
	private $patch_data;

	/**
	 * WCPaymentPatch constructor.
	 *
	 * @param PaymentPatchData $patch_data You guessed it: The Patch data.
	 */
	public function __construct( PaymentPatchData $patch_data ) {

		$this->patch_data = $patch_data;
	}

	/**
	 * Execute the PatchRequest
	 *
	 * @return bool
	 */
	public function execute() {

		$patch_request = $this->patch_data->get_patch_request();
		try {
			$payment = $this->patch_data->get_payment();
			$result  = $payment->update( $patch_request, $this->patch_data->get_api_context() );
			if ( $result ) {
				return true;
			}
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'wc_paypal_plus_log_exception', 'payment_patch_exception', $ex );

			return false;
		}

		return false;
	}

}
