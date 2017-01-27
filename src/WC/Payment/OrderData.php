<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 17:16
 */

namespace PayPalPlusPlugin\WC\Payment;

class OrderData extends OrderDataCommon {

	use OrderDataProcessor;
	/**
	 * @var \WC_Order
	 */
	private $order;

	public function __construct( \WC_Order $order ) {

		$this->order = $order;
	}

	public function get_items() {

		$cart  = $this->order->get_items();
		$items = [];
		foreach ( $cart as $item ) {
			$items[] = new OrderItemData( $item );
		}

		if ( $this->get_total_discount() > 0 ) {
			$items[] = new OrderDiscountData( [
				'name'          => 'Total Discount',
				'qty'           => 1,
				'line_subtotal' => - $this->format( $this->get_total_discount() ),
				//'number' => implode( ", ", $this->order->get_used_coupons() ),
			] );
		}

		return $items;
	}

	public function get_total_discount() {

		return $this->order->get_total_discount();
	}

	public function get_total_tax() {

		//if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
		//	$tax = 0;
		//} else {
		//	$tax = $this->order->get_total_tax();
		//}
		$tax = $this->order->get_total_tax();

		return $tax;
	}

	public function get_total_shipping() {

		if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' && ! ( 'yes' === get_option( 'woocommerce_calc_taxes' ) && 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) ) {
			$shipping = $this->order->get_total_shipping() + $this->order->get_shipping_tax();
		} else {
			$shipping = $this->order->get_total_shipping();
		}

		return $shipping;
	}
}