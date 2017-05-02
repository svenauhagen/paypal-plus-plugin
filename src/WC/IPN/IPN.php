<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.11.16
 * Time: 10:46
 */

namespace WCPayPalPlus\WC\IPN;

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
		$this->data       = $data ?: new IPNData( filter_input_array( INPUT_POST ) ?: [] );

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

		return add_action( 'woocommerce_api_' . $this->get_api_endpoint(), [ $this, 'check_response' ] );

	}

	/**
	 * Returns the endpoint used for IPN requests
	 *
	 * @return string
	 */
	private function get_api_endpoint() {

		return $this->gateway_id . '_ipn';

	}

	/**
	 * Returns the Notification URL
	 *
	 * @return string
	 */
	public function get_notify_url() {

		return WC()->api_request_url( $this->get_api_endpoint() );

	}

	/**
	 * Check for PayPal IPN Response.
	 */
	public function check_response() {

		if (
			$this->validator->validate()
			&& ! empty( $this->data->get( 'custom' ) )
			&& ( $order = $this->data->get_woocommerce_order() )
		) {
			$this->valid_response();
			exit;
		}
		do_action( 'wc_paypal_plus_log_error', 'Invalid IPN call', $this->data->get_all() );
		wp_die( 'PayPal IPN Request Failure', 'PayPal IPN', [ 'response' => 500 ] );
	}

	/**
	 * There was a valid response.
	 */
	public function valid_response() {

		$payment_status = $this->data->get_payment_status();
		$updater        = $this->data->get_order_updater();
		if ( method_exists( $updater, 'payment_status_' . $payment_status ) ) {
			do_action(
				'wc_paypal_plus_log', 'Processing IPN. payment status: ' . $payment_status,
				$this->data->get_all()
			);
			call_user_func( [ $updater, 'payment_status_' . $payment_status ] );

			return true;
		}

		return false;
	}

}
