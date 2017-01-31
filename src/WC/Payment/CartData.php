<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 17.01.17
 * Time: 15:53
 */

namespace PayPalPlusPlugin\WC\Payment;

/**
 * Class CartData
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
class CartData extends OrderDataCommon {

	use OrderDataProcessor;
	/**
	 * WooCommerce Cart.
	 *
	 * @var \WC_Cart
	 */
	private $cart;

	/**
	 * CartData constructor.
	 *
	 * @param \WC_Cart $cart WooCommerce cart.
	 */
	public function __construct( \WC_Cart $cart ) {

		$this->cart = $cart;
	}

	/**
	 * Return the total tax amouunt of the cart.
	 *
	 * @return float
	 */
	public function get_total_tax() {

		$tax = $this->cart->get_taxes_total();

		return $tax;
	}

	/**
	 * Returns an array of item data providers.
	 *
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
					'name'          => 'Cart Discount',
					'qty'           => '1',
					'line_subtotal' => '-' . $this->format( $this->cart->coupon_discount_amounts[ $code ] ),
				] );
			}
		}

		return $items;
	}

	/**
	 * Returns the total discount in the cart.
	 *
	 * @return float
	 */
	public function get_total_discount() {

		return $this->cart->get_cart_discount_total();

	}

	/**
	 * Returns the total shipping cost.
	 *
	 * @return float
	 */
	public function get_total_shipping() {

		if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
			$shipping = $this->cart->shipping_total + $this->cart->shipping_tax_total;
		} else {
			$shipping = $this->cart->shipping_total;
		}

		return $shipping;
	}
}
