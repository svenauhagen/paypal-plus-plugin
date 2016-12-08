<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 16:43
 */

namespace PayPalPlusPlugin\WC\IPN;

class IPNData {

	/**
	 * @var array
	 */
	private $request;
	/**
	 * @var string
	 */
	private $paypal_url;
	private $sandbox;
	private $updater;

	/**
	 * IPNData constructor.
	 *
	 * @param array $request
	 * @param       $sandbox
	 */
	public function __construct( array $request = [], $sandbox = TRUE ) {

		$this->request    = $request;
		$this->paypal_url = $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
			: 'https://www.paypal.com/cgi-bin/webscr';
		$this->sandbox    = $sandbox;
	}

	/**
	 * @return string
	 */
	public function get_paypal_url() {

		return $this->paypal_url;
	}

	/**
	 * @return string
	 */
	public function get_payment_status() {

		$status = $this->get( 'payment_status' );

		if ( $this->get( 'test_ipn', FALSE )
		     && 'pending' == $status
		) {
			$status = 'completed';
		}

		return strtolower( $status );

	}

	/**
	 * @return OrderUpdater
	 */
	public function get_order_updater() {

		if ( is_null( $this->updater ) ) {
			$this->updater = new OrderUpdater( $this->get_paypal_order(), $this );
		}

		return $this->updater;

	}

	/**
	 * @return array
	 */
	public function get_all() {

		return $this->request;

	}

	/**
	 * @return string
	 */
	public function get_user_agent() {

		return 'WooCommerce/' . get_wc_version();
	}

	/**
	 * Get the order from the PayPal 'Custom' variable.
	 *
	 *
	 * @return \WC_Order
	 */
	public function get_paypal_order() {

		if ( ! $raw_custom = $this->get( 'custom', FALSE ) ) {
			return NULL;
		}
		if ( ( $custom = json_decode( $raw_custom ) ) && is_object( $custom ) ) {
			$order_id  = $custom->order_id;
			$order_key = $custom->order_key;
		} elseif ( preg_match( '/^a:2:{/', $raw_custom )
		           && ! preg_match( '/[CO]:\+?[0-9]+:"/', $raw_custom )
		           && ( $custom = maybe_unserialize( $raw_custom ) )
		) {
			$order_id  = $custom[0];
			$order_key = $custom[1];
		} else {

			return NULL;
		}
		if ( ! $order = wc_get_order( $order_id ) ) {
			$order_id = wc_get_order_id_by_order_key( $order_key );
			$order    = wc_get_order( $order_id );
		}
		if ( ! $order || $order->order_key !== $order_key ) {

			return NULL;
		}

		return $order;
	}

	/**
	 * @param $offset
	 *
	 * @return bool
	 */
	public function has( $offset ) {

		return isset( $this->request[ $offset ] );
	}

	/**
	 * @param        $offset
	 * @param string $fallback
	 *
	 * @return mixed|string
	 */
	public function get( $offset, $fallback = '' ) {

		if ( $this->has( $offset ) ) {
			return $this->request[ $offset ];

		}

		return $fallback;
	}

}