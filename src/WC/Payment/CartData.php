<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 17.01.17
 * Time: 15:53
 */

namespace PayPalPlusPlugin\WC\Payment;

class CartData extends OrderDataCommon {

	use OrderDataProcessor;
	/**
	 * @var \WC_Cart
	 */
	private $cart;

	public function __construct( \WC_Cart $cart ) {

		$this->cart = $cart;
	}

	public function get_total() {

		$total = $this->get_subtotal();

		$tax = $this->format( $this->get_total_tax() );
		$total += $tax;

		return $this->round( $total );
	}



	/**
	 * @return OrderItemDataProvider[]
	 */
	public function get_items() {

		$cart  = $this->cart->get_cart();
		$items = [];
		foreach ( $cart as $item ) {
			$items[] = new CartItemData( $item );
		}

		return $items;
	}

	public function get_total_tax() {

		//if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
		//	$tax = 0;
		//} else {
		//	$tax = $this->cart->get_taxes_total();
		//}

		$tax = $this->cart->get_taxes_total();

		return $tax;
	}

	public function get_total_shipping() {

		if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' && ! ( 'yes' === get_option( 'woocommerce_calc_taxes' ) && 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) ) {
			$shipping = $this->cart->shipping_total + WC()->cart->shipping_tax_total;
		} else {
			$shipping = $this->cart->shipping_total;
		}

		return $shipping;
	}
}