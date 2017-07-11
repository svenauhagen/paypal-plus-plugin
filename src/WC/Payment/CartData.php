<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 17.01.17
 * Time: 15:53
 */

namespace WCPayPalPlus\WC\Payment;

/**
 * Class CartData
 *
 * @package WCPayPalPlus\WC\Payment
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

		$tax = $this->format( $this->round( $this->cart->get_taxes_total( true, false ) ) );

		return $tax;
	}

	/**
	 * Returns the total shipping cost.
	 *
	 * @return float
	 */
	public function get_total_shipping() {

		$shipping = $this->cart->shipping_total;

			// If shipping tax exists, and shipping has more than 2 decimals
			// Then calculate rounded shipping amount to prevent rounding errors
		if ( $this->get_shipping_tax() && preg_match( '/\.\d{3,}/', $shipping ) ) {
			$shipping_tax = $this->cart->shipping_tax_total;
			$shipping_total = $this->round( $shipping + $shipping_tax );
			$shipping = $shipping_total - $this->round( $shipping_tax );
		}

		return $this->format( $this->round( $shipping ) );
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
					// TODO: Maybe we want to add the discount name here..?
					'name'          => 'Cart Discount',
					'qty'           => '1',
					'line_subtotal' => '-' . $this->format( $this->cart->coupon_discount_amounts[ $code ] ),
				] );
			}
		}

		foreach ( $this->cart->get_fees() as $fee ) {
			$items[] = new FeeData( $fee );
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
		 * Get total shipping tax.
		 *
		 * @return string
		 */
	public function get_shipping_tax() {
		return $this->format( $this->round( $this->cart->shipping_tax_total ) );
	}

	/**
		 * Get the subtotal including any additional taxes.
		 *
		 * This is used when the prices are given already including tax.
		 *
		 * @return string
		 */
	protected function get_subtotal_including_tax() {
		return $this->format($this->round(
			$this->cart->cart_contents_total
			+ $this->cart->fee_total
			+ $this->cart->tax_total
		) );
	}

}
