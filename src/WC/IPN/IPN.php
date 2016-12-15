<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.11.16
 * Time: 10:46
 */

namespace PayPalPlusPlugin\WC\IPN;

/**
 * Handles responses from PayPal IPN.
 */
class IPN {

	/**
	 * ID of this Payment gateway
	 *
	 * @var string
	 */
	private $gateway_id;
	/**
	 * IPN Data Provider
	 *
	 * @var IPNData
	 */
	private $data;
	/**
	 * IPN Validator class
	 *
	 * @var IPNValidator
	 */
	private $validator;

	/**
	 * Constructor.
	 *
	 * @param string       $gateway_id The Gateway ID.
	 *
	 * @param IPNData      $data       IPN Data provider.
	 *
	 * @param IPNValidator $validator  IPN Data Validator.
	 */
	public function __construct(
		$gateway_id,
		IPNData $data = null,
		IPNValidator $validator = null
	) {

		$this->gateway_id = $gateway_id;
		$this->data       = $data ?: new IPNData( $_POST );

		$this->validator = $validator
			?: new IPNValidator(
				$this->data->get_all(),
				$this->data->get_paypal_url(),
				$this->data->get_user_agent()
			);

	}

	/**
	 * Register WP/WC Hooks
	 */
	public function register() {

		add_action( 'woocommerce_api_' . $this->gateway_id, array( $this, 'check_response' ) );

	}

	/**
	 * Returns the Notification URL
	 *
	 * @return string
	 */
	public function get_notify_url() {

		return WC()->api_request_url( $this->gateway_id );

	}

	/**
	 * Check for PayPal IPN Response.
	 */
	public function check_response() {

		if ( $this->data->has( 'ipn_track_id' ) ) {
			if (
				$this->validator->validate()
				&& ! empty( $this->data->get( 'custom' ) )
				&& ( $order = $this->data->get_paypal_order() )
			) {
				$this->valid_response();
				exit;
			}
			wp_die( 'PayPal IPN Request Failure', 'PayPal IPN', array( 'response' => 500 ) );
		}
	}

	/**
	 * There was a valid response.
	 */
	public function valid_response() {

		$payment_status = $this->data->get_payment_status();
		$updater        = $this->data->get_order_updater();
		if ( method_exists( $updater, 'payment_status_' . $payment_status ) ) {
			call_user_func( [ $updater, 'payment_status_' . $payment_status ] );

			return true;
		}

		return false;
	}

}
