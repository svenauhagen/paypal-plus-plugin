<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 17:32
 */

namespace WCPayPalPlus\WC\IPN;

/**
 * Class IPNValidator
 *
 * @package WCPayPalPlus\WC\IPN
 */
class IPNValidator {

	/**
	 * Request data.
	 *
	 * @var array
	 */
	private $request_data;
	/**
	 * URL for remote calls
	 *
	 * @var string
	 */
	private $paypal_url;
	/**
	 * The user agent
	 *
	 * @var string
	 */
	private $user_agent;

	/**
	 * IPNValidator constructor.
	 *
	 * @param array  $request_data Request Data array.
	 * @param string $paypal_url   URL to use in PayPal calls.
	 * @param string $user_agent   User Agent to use in payPal calls.
	 */
	public function __construct( array $request_data, $paypal_url, $user_agent ) {

		$this->request_data = $request_data;
		$this->paypal_url   = $paypal_url;
		$this->user_agent   = $user_agent;
	}

	/**
	 * Validates an IPN Request
	 *
	 * @return bool
	 */
	public function validate() {

		if ( defined( 'PPP_DEBUG' ) and PPP_DEBUG ) {
			return true;
		}
		$params = [
			'body'        => [ 'cmd' => '_notify-validate' ] + $this->request_data,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => $this->user_agent,
		];

		$response = wp_safe_remote_post( $this->paypal_url, $params );

		if ( $response instanceof \WP_Error ) {
			return false;
		}
		if ( ! isset( $response['response']['code'] ) ) {
			return false;
		}
		if ( $response['response']['code'] >= 200 && $response['response']['code'] < 300
		     && strstr( $response['body'], 'VERIFIED' )
		) {
			return true;
		}

		return false;
	}
}
