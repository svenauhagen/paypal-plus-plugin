<?php
namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Item;

abstract class OrderDataCommon implements OrderDataProvider {

	use OrderDataProcessor;

	public function get_total() {

		$total    = $this->get_subtotal();
		$tax      = $this->format( $this->get_total_tax() );
		$discount = $this->get_total_discount();

		//$total -= $discount;
		$total += $tax;

		return $this->round( $total );
	}

	public function get_subtotal() {

		$subtotal = 0;
		$items    = $this->get_items();
		foreach ( $items as $item ) {
			$product_price = $item->get_price();
			$item_price    = $product_price * $item->get_quantity();
			$subtotal += $this->round( $item_price );
		}

		return $subtotal;
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