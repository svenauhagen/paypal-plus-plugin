<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 12:13
 */

namespace PayPalPlusPlugin\WC;

use PayPal\Api\Payment;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Exception\PayPalInvalidCredentialException;
use PayPal\Rest\ApiContext;

class CredentialVerification {

	/**
	 * @var ApiContext
	 */
	private $context;
	/**
	 * @var string
	 */
	private $error;

	/**
	 * CredentialVerification constructor.
	 *
	 * @param ApiContext $context
	 */
	public function __construct( $context ) {

		$this->context = $context;
	}

	public function verify() {

		$api_context = $this->context;
		if ( is_null( $api_context ) ) {
			return FALSE;
		}
		try {
			$params = array( 'count' => 1 );
			Payment::all( $params, $api_context );
		} catch ( PayPalInvalidCredentialException $ex ) {
			do_action( 'paypal_plus_plugin_log', 'credential_exception', $ex );
			$this->error = $ex->getMessage();

			return FALSE;
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'paypal_plus_plugin_log', 'credential_exception', $ex );
			$this->error = $ex->getMessage();

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @return string
	 */
	public function get_error_message() {

		return $this->error;
	}
}