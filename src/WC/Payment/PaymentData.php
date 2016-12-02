<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 16:42
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Rest\ApiContext;

/**
 * Class PaymentData
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
class PaymentData {

	/**
	 * @var string
	 */
	private $return_url;
	/**
	 * @var string
	 */
	private $cancel_url;
	/**
	 * @var string
	 */
	private $notify_url;
	/**
	 * @var string
	 */
	private $web_profile_id;
	/**
	 * @var ApiContext
	 */
	private $api_context;

	/**
	 * PaymentData constructor.
	 *
	 * @param string     $return_url
	 * @param string     $cancel_url
	 * @param string     $notify_url
	 * @param string     $web_profile_id
	 * @param ApiContext $api_context
	 */
	public function __construct(
		$return_url,
		$cancel_url,
		$notify_url,
		$web_profile_id,
		ApiContext $api_context
	) {

		$this->return_url     = $return_url;
		$this->cancel_url     = $cancel_url;
		$this->notify_url     = $notify_url;
		$this->web_profile_id = $web_profile_id;
		$this->api_context = $api_context;
	}

	/**
	 * @return string
	 */
	public function get_notify_url() {

		return $this->notify_url;
	}

	/**
	 * @return string
	 */
	public function get_cancel_url() {

		return $this->cancel_url;
	}

	/**
	 * @return string
	 */
	public function get_return_url() {

		return $this->return_url;
	}

	/**
	 * @return string
	 */
	public function get_web_profile_id() {

		return $this->web_profile_id;
	}

	/**
	 * @return ApiContext
	 */
	public function get_api_context() {

		return $this->api_context;
	}

}