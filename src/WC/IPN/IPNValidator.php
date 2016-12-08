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
	 * IPNValidator constructor.
	 *
	 * @param IPNData $data
	 */
	public function __construct( IPNData $data ) {

		$this->data = $data;
	}

	public function validate() {

		$data = [ 'cmd' => '_notify-validate' ] + $this->data->get_all();

		$params = [
			'body'        => $data,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => FALSE,
			'decompress'  => FALSE,
			'user-agent'  => $this->data->get_user_agent(),
		];

		$response = wp_safe_remote_post( $this->data->get_paypal_url(), $params );
		if ( ! ( $response instanceof \WP_Error ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300
		     && strstr( $response['body'], 'VERIFIED' )
		) {
			return TRUE;
		}

		return FALSE;
	}
}