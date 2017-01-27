<?php
namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Item;

abstract class OrderDataCommon implements OrderDataProvider {

	public function get_subtotal() {

		$subtotal = 0;
		$items = $this->get_items();
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

		$product  = $data->get_product();
		$name     = html_entity_decode( $product->get_title(), ENT_NOQUOTES, 'UTF-8' );
		$currency = get_woocommerce_currency();
		$sku      = $product->get_sku();
		$price    = $data->get_price();
		if ( $product instanceof \WC_Product_Variation ) {
			$sku = $product->parent->get_sku();
		}
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