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
	 * @var string
	 */
	private $gateway_id;
	/**
	 * @var IPNData
	 */
	private $data;
	/**
	 * @var IPNValidator
	 */
	private $validator;

	/**
	 * Constructor.
	 *
	 * @param              $gateway_id
	 *
	 * @param IPNData      $data
	 *
	 * @param IPNValidator $validator
	 *
	 */
	public function __construct(
		$gateway_id,
		IPNData $data = NULL,
		IPNValidator $validator = NULL
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

	public function register() {

		add_action( 'woocommerce_api_' . $this->gateway_id, array( $this, 'check_response' ) );

	}

	public function get_notify_url() {

		return WC()->api_request_url( $this->gateway_id );

	}

	/**
	 * Check for PayPal IPN Response.
	 */
	public function check_response() {

		if ( isset( $_POST['ipn_track_id'] ) && ! empty( $_POST['ipn_track_id'] ) ) {
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
	 *
	 */
	public function valid_response() {

		$payment_status = $this->data->get_payment_status();
		$updater        = $this->data->get_order_updater();
		if ( method_exists( $updater, 'payment_status_' . $payment_status ) ) {
			call_user_func( [ $updater, 'payment_status_' . $payment_status ] );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Send a notification to the user handling orders.
	 *
	 * @param string $subject
	 * @param string $message
	 */
	protected function send_ipn_email_notification( $subject, $message ) {

		$new_order_settings = get_option( 'woocommerce_new_order_settings', [] );
		$mailer             = WC()->mailer();
		$message            = $mailer->wrap_message( $subject, $message );
		$mailer->send( ! empty( $new_order_settings['recipient'] ) ? $new_order_settings['recipient']
			: get_option( 'admin_email' ), strip_tags( $subject ), $message );
	}

}