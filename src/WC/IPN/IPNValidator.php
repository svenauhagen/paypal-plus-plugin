<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 17:32
 */

namespace PayPalPlusPlugin\WC\IPN;

class IPNValidator {

	/**
	 * @var IPNData
	 */
	private $data;
	/**
	 * @var array
	 */
	private $request_data;
	private $paypal_url;
	private $user_agent;

	/**
	 * IPNValidator constructor.
	 *
	 * @param array  $request_data
	 * @param string $paypal_url
	 * @param string $user_agent
	 */
	public function __construct( array $request_data, $paypal_url, $user_agent ) {

		$this->request_data = [ 'cmd' => '_notify-validate' ] + $request_data;
		$this->paypal_url   = $paypal_url;
		$this->user_agent   = $user_agent;
	}

	public function validate() {

		$params = [
			'body'        => $this->request_data,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => FALSE,
			'decompress'  => FALSE,
			'user-agent'  => $this->user_agent,
		];

		$response = wp_safe_remote_post( $this->paypal_url, $params );

		if ( $response instanceof \WP_Error ) {
			return FALSE;
		}
		if ( ! isset( $response['response']['code'] ) ) {
			return FALSE;
		}
		if ( $response['response']['code'] >= 200 && $response['response']['code'] < 300
		     && strstr( $response['body'], 'VERIFIED' )
		) {
			return TRUE;
		}

		return FALSE;
	}
}