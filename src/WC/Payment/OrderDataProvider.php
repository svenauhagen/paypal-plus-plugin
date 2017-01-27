<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 17.01.17
 * Time: 15:52
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Item;

/**
 * Interface OrderDataProvider
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
interface OrderDataProvider {

	/**
	 * @return OrderItemDataProvider[]
	 */
	public function get_items();

	/**
	 * @return float
	 */
	public function get_subtotal();

	/**
	 * @return float
	 */
	public function get_total();

	/**
	 * @return float
	 */
	public function get_total_tax();

	/**
	 * @return float
	 */
	public function get_total_shipping();

	/**
	 * @return float
	 */
	public function get_total_discount();

	/**
	 * Creates a single Order Item for the Paypal API
	 *
	 * @param OrderItemDataProvider $item Order|Cart item.
	 *
	 * @return Item
	 */
	public function get_item( OrderItemDataProvider $item );
}