<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 17:16
 */

namespace PayPalPlusPlugin\WC\Payment;

/**
 * Class OrderData
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
/**
 * Class OrderData
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
class OrderData extends OrderDataCommon {

	use OrderDataProcessor;
	/**
	 * WooCommerce Order object.
	 *
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * OrderData constructor.
	 *
	 * @param \WC_Order $order WooCommerce order object.
	 */
	public function __construct( \WC_Order $order ) {

		$this->order = $order;
	}

	/**
	 * Returns an array of item data providers.
	 *
	 * @return OrderItemDataProvider[]
	 */
	public function get_items() {

		$cart  = $this->order->get_items();
		$items = [];
		foreach ( $cart as $item ) {
			$items[] = new OrderItemData( $item );
		}
		foreach ( $this->order->get_fees() as $fee ) {
			$items[] = new OrderFeeData( [
				'name'          => $fee['name'],
				'qty'           => 1,
				'line_subtotal' => $fee['line_total'],
			] );
		}
		if ( ( $discount = $this->get_total_discount() ) > 0 ) {
			$items[] = new OrderDiscountData( [
				'name'          => 'Total Discount',
				'qty'           => 1,
				'line_subtotal' => - $this->format( $discount ),
			] );
		}

		return $items;
	}

	/**
	 * Returns the total discount on the order.
	 *
	 * @return float
	 */
	public function get_total_discount() {

		return $this->order->get_total_discount();
	}

	/**
	 * Returns the total tax amount of the order.
	 *
	 * @return float
	 */
	public function get_total_tax() {

		$tax = $this->order->get_total_tax();

		return $tax;
	}

	/**
	 * Returns the total shipping cost of the order.
	 *
	 * @return float
	 */
	public function get_total_shipping() {

		//if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
		//	$shipping = $this->order->get_total_shipping() + $this->order->get_shipping_tax();
		//} else {
		$shipping = $this->order->get_total_shipping();

		//}

		return $shipping;
	}
}
