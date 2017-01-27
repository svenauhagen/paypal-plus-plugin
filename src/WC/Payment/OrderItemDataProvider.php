<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 26.01.17
 * Time: 14:06
 */

namespace PayPalPlusPlugin\WC\Payment;

interface OrderItemDataProvider {

	/**
	 * @return float
	 */
	public function get_price();

	/**
	 * @return int
	 */
	public function get_quantity();

	/**
	 * @return string
	 */
	public function get_name();

	/**
	 * @return string|null
	 */
	public function get_sku();

}