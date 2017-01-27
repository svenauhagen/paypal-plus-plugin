<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 17.01.17
 * Time: 15:52
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Item;

interface OrderDataProvider {

	/**
	 * @return OrderItemDataProvider[]
	 */
	public function get_items();

	public function get_subtotal();

	public function get_total();

	public function get_total_tax();

	public function get_total_shipping();

	/**
	 * Creates a single Order Item for the Paypal API
	 *
	 * @param array $item Order|Cart item.
	 *
	 * @return Item
	 */
	public function get_item( OrderItemDataProvider $item );
}