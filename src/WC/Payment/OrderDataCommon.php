<?php
namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Item;
use PayPal\Api\ItemList;

/**
 * Class OrderDataCommon
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
abstract class OrderDataCommon implements OrderDataProvider {

	use OrderDataProcessor;

	/**
	 * Calculate the order total.
	 *
	 * @return float
	 */
	public function get_total() {

		$total    = $this->get_subtotal();
		$tax      = $this->get_total_tax();
		$shipping = $this->get_total_shipping();

		$total += $shipping;
		$total += $tax;

		return $total;
	}

	/**
	 * Calculate the order subtotal.
	 *
	 * TODO There are rare cases where the rounded subtotal woocommerce gives us is not the same as the sum of all
	 * order items. This leads to an error by PayPal, because the total amount is off by 1 cent. There is some rounding
	 * error involved that needs to be investigated.
	 *
	 * @return float
	 */
	public function get_subtotal() {

		$subtotal = 0;
		$items    = $this->get_item_list()
		                 ->getItems();
		if ( empty( $items ) ) {
			return $subtotal;
		}
		foreach ( $items as $item ) {
			$product_price = $item->getPrice();
			$item_price    = floatval( $product_price * $item->getQuantity() );
			$subtotal += $item_price;
			//$subtotal += $this->round( $item_price );
		}

		return $subtotal;
		//return $this->round($subtotal);
	}

	/**
	 * Generated a new ItemList object from the items of the current order
	 *
	 * @return ItemList
	 */
	public function get_item_list() {

		$item_list = new ItemList();
		foreach ( $this->get_items() as $order_item ) {

			$item_list->addItem( $this->get_item( $order_item ) );
		}

		return $item_list;
	}

	/**
	 * Creates a single Order Item for the Paypal API
	 *
	 * @param OrderItemDataProvider $data Order|Cart item.
	 *
	 * @return Item
	 */
	public function get_item( OrderItemDataProvider $data ) {

		$name     = html_entity_decode( $data->get_name(), ENT_NOQUOTES, 'UTF-8' );
		$currency = get_woocommerce_currency();
		$sku      = $data->get_sku();
		$price    = $data->get_price();

		$item = new Item();

		$item->setName( $name )
		     ->setCurrency( $currency )
		     ->setQuantity( $data->get_quantity() )
		     ->setPrice( $price );

		if ( ! empty( $sku ) ) {
			$item->setSku( $sku );// Similar to `item_number` in Classic API.
		}

		return $item;
	}
}
