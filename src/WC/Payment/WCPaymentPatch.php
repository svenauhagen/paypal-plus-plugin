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
	 * @return bool
	 */
	public function execute() {

		$patch_request = $this->patch_data->get_patch_request();
		try {
			$payment = $this->patch_data->get_payment();
			$result  = $payment->update( $patch_request, $this->patch_data->get_api_context() );
			if ( $result == TRUE ) {
				return TRUE;
			}
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'paypal_plus_plugin_log', 'payment_patch_exception', $ex );

			return FALSE;
		}

		return FALSE;
	}



}