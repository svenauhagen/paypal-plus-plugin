<?php

namespace WCPayPalPlus\WC\Payment;

use Inpsyde\Lib\PayPal\Api\Item;
use Inpsyde\Lib\PayPal\Api\ItemList;

/**
 * Class OrderDataCommon
 *
 * @package WCPayPalPlus\WC\Payment
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
		$shipping = $this->get_total_shipping();
		if ( $this->should_include_tax_in_total() ) {
			$tax      = $this->get_total_tax();
			$total += $tax;
		} else {
			$shipping += $this->get_shipping_tax();
		}
		$total += $shipping;

		$total = $this->format( $total );

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

		if ( $this->should_include_tax_in_total() ) {
			$subtotal = 0;
			$items    = $this->get_item_list()
			                 ->getItems();
			if ( empty( $items ) ) {
				return $subtotal;
			}
			foreach ( $items as $item ) {
				$product_price = $item->getPrice();
				$item_price    = floatval( $product_price * $item->getQuantity() );
				$subtotal      += $item_price;
			}
		} else {
			$subtotal = $this->get_subtotal_including_tax();
		}

		return $this->format( $subtotal );
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
	
	/**
		 * Whether to list taxes in addition to the subtotal.
		 *
		 * @return bool
		 */
	public function should_include_tax_in_total() {
		return ( ! wc_tax_enabled() || ! wc_prices_include_tax() );
	}
	
	/**
		 * Get total shipping tax.
		 *
		 * @return string
		 */
	abstract public function get_shipping_tax();
	
	/**
		 * Get the subtotal including any additional taxes.
		 *
		 * This is used when the prices are given already including tax.
		 *
		 * @return string
		 */
	abstract protected function get_subtotal_including_tax();
	
}
