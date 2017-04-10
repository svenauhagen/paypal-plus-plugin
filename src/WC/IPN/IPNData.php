<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 16:43
 */

namespace WCPayPalPlus\WC\IPN;

/**
 * Class IPNData
 *
 * @package WCPayPalPlus\WC\IPN
 */
class IPNData {

	/**
	 * Request data
	 *
	 * @var array
	 */
	private $request;
	/**
	 * URL to use for PayPal calls
	 *
	 * @var string
	 */
	private $paypal_url;
	/**
	 * Sandbox flag
	 *
	 * @var bool
	 */
	private $sandbox;
	/**
	 * Order update handler
	 *
	 * @var OrderUpdater
	 */
	private $updater;

	/**
	 * IPNData constructor.
	 *
	 * @param array $request Request data.
	 * @param bool  $sandbox Flag to set sandbox mode.
	 */
	public function __construct( array $request = [], $sandbox = true ) {

		$this->request    = $request;
		$this->paypal_url = $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
			: 'https://www.paypal.com/cgi-bin/webscr';
		$this->sandbox    = $sandbox;
	}

	/**
	 * Returns the URL to the PayPal service
	 *
	 * @return string
	 */
	public function get_paypal_url() {

		return $this->paypal_url;
	}

	/**
	 * Returns the current status
	 *
	 * @return string
	 */
	public function get_payment_status() {

		return strtolower( $this->get( 'payment_status' ) );

	}

	/**
	 * Fetches a specific value by key
	 *
	 * @param string $offset   The key to fetch from data.
	 * @param string $fallback Fallback value to return if the value wasn't found.
	 *
	 * @return mixed|string
	 */
	public function get( $offset, $fallback = '' ) {

		if ( $this->has( $offset ) ) {
			return $this->request[ $offset ];

		}

		return $fallback;
	}

	/**
	 * Checks if a specific value exists
	 *
	 * @param string $offset The key to search.
	 *
	 * @return bool
	 */
	public function has( $offset ) {

		return isset( $this->request[ $offset ] );
	}

	/**
	 * Returns the Order Update handler
	 *
	 * @return OrderUpdater
	 */
	public function get_order_updater() {

		if ( is_null( $this->updater ) ) {
			$this->updater = new OrderUpdater( $this->get_woocommerce_order(), $this );
		}

		return $this->updater;

	}

	/**
	 * Get the order from the PayPal 'Custom' variable.
	 * TODO Passing and decoding custom data should by handled by one set of objects.
	 * TODO Right now, this is implemented in 2 completely separate places
	 *
	 * @return \WC_Order
	 */
	public function get_woocommerce_order() {

		if ( ! $raw_custom = $this->get( 'custom', false ) ) {
			return null;
		}
		if ( ( $custom = json_decode( $raw_custom ) ) && is_object( $custom ) ) {
			$order_id  = $custom->order_id;
			$order_key = $custom->order_key;
		} else {

			return null;
		}
		if ( ! $order = wc_get_order( $order_id ) ) {
			$order_id = wc_get_order_id_by_order_key( $order_key );
			$order    = wc_get_order( $order_id );
		}
		if ( ! $order ) {

			return null;
		}

		return $order;
	}

	/**
	 * Returns all request data
	 *
	 * @return array
	 */
	public function get_all() {

		return $this->request;

	}

	/**
	 * Returns the UA to use in PayPal calls
	 *
	 * @return string
	 */
	public function get_user_agent() {

		return 'WooCommerce/' . WC()->version;
	}

}
