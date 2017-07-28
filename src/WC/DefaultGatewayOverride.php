<?php # -*- coding: utf-8 -*-

namespace WCPayPalPlus\WC;

/**
 * Class DefaultGatewayOverride
 *
 * Overrides the default Payment Gateway ONCE per user session.
 *
 * Hence, it should never override user input.
 */
class DefaultGatewayOverride {

	/**
	 * @var string
	 */
	private $gateway_id = 'paypal_plus';

	/**
	 * @var string
	 */
	private $session_check_key = '_ppp_default_override_flag';

	/**
	 * DefaultGatewayOverride constructor.
	 *
	 * @param string $gateway_id
	 */
	public function __construct( $gateway_id ) {

		$this->gateway_id = $gateway_id;
	}

	/**
	 * Override the gateway if this is a vaild request
	 */
	public function init() {

		add_action( 'wp', function () {

			if ( $this->is_valid_request() ) {
				$this->override_gateway();
				$this->set_session_flag();
			}
		} );

	}

	/**
	 * Check the current request
	 *
	 * @return bool
	 */
	public function is_valid_request() {

		if ( is_ajax() ) {
			return false;
		}

		if ( isset( $_POST['payment_method'] ) ) {
			return false;
		}
		if ( ! is_checkout() ) {
			return false;
		}

		if ( $this->get_session_flag() ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve our private session flag
	 *
	 * @return array|string
	 */
	private function get_session_flag() {

		return WC()->session->get( $this->session_check_key );
	}

	/**
	 * Set the gateway override
	 */
	private function override_gateway() {

		WC()->session->set( 'chosen_payment_method', $this->gateway_id );
	}

	/**
	 * Set our private session flag
	 */
	private function set_session_flag() {

		WC()->session->set( $this->session_check_key, '1' );
	}
}