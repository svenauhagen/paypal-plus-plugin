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



	public function get_total_tax() {

		//if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
		//	$tax = 0;
		//} else {
		//	$tax = $this->cart->get_taxes_total();
		//}

		$tax = $this->cart->get_taxes_total();

		return $tax;
	}

	public function get_total_discount() {

		return $this->cart->get_cart_discount_total();

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
		if ( $this->get_total_discount() > 0 ) {
			foreach ( $this->cart->get_coupons( 'cart' ) as $code => $coupon ) {
				$items[] = new OrderDiscountData( [
					'name'   => 'Cart Discount',
					'qty'    => '1',
					//'number' => $code,
					'line_subtotal'    => '-' . $this->format( WC()->cart->coupon_discount_amounts[ $code ] ),
				] );
			}
		}

		return $items;
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