<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 17.01.17
 * Time: 15:52
 */

namespace WCPayPalPlus\WC\Payment;

use Inpsyde\Lib\PayPal\Api\Item;
use Inpsyde\Lib\PayPal\Api\ItemList;

/**
 * Interface OrderDataProvider
 *
 * @package WCPayPalPlus\WC\Payment
 */
interface OrderDataProvider {

	/**
	 * Array of item data providers.
	 *
	 * @return OrderItemDataProvider[]
	 */
	public function get_items();

	/**
	 * Array of item data providers.
	 *
	 * @return ItemList
	 */
	public function get_item_list();

	/**
	 * Order subtotal.
	 *
	 * @return float
	 */
	public function get_subtotal();

	/**
	 * Order total.
	 *
	 * @return float
	 */
	public function get_total();

	/**
	 * Tax total amount.
	 *
	 * @return float
	 */
	public function get_total_tax();

	/**
	 * Total shipping cost.
	 *
	 * @return float
	 */
	public function get_total_shipping();

	/**
	 * Total discount amount.
	 *
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
