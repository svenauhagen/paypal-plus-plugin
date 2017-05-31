<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 31.01.17
 * Time: 16:13
 */

namespace WCPayPalPlus\WC\Payment;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use WCPayPalPlus\Test\PriceFormatter;
use WCPayPalPlus\Test\WCCartMock;

class CartDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_total(
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		$cart  = WCCartMock::getMock(
			'get_total',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);
		$data  = new CartData( $cart );
		$total = $data->get_total();
		$this->assertSame( round( $cart_total ), round( $total ) );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_items(
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		$cart = WCCartMock::getMock(
			'get_items',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);

		$data  = new CartData( $cart );
		$items = $data->get_items();

		$this->assertContainsOnlyInstancesOf( OrderItemDataProvider::class, $items );
		$expectedCount = count( $rawItems );
		$expectedCount += count( $fees );
		$expectedCount += ( $discount > 0 ) ? 1 : 0;

		$this->assertEquals( $expectedCount, count( $items ) );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_total_discount(
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		$cart = WCCartMock::getMock(
			'get_total_discount',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);

		$data   = new CartData( $cart );
		$result = $data->get_total_discount();
		$this->assertSame( $discount, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_total_tax(
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		$cart = WCCartMock::getMock(
			'get_total_tax',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);

		$data     = new CartData( $cart );
		$result   = $data->get_total_tax();
		$expected = PriceFormatter::format( $tax );
		$this->assertSame( $expected, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_total_shipping(
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {
		Functions::expect( 'get_woocommerce_currency' );
		$cart = WCCartMock::getMock(
			'get_total_shipping',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);

		$data   = new CartData( $cart );
		$result = $data->get_total_shipping();
		$expected = PriceFormatter::format( $shipping );
		$this->assertSame( $expected, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_subtotal(
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		$cart = WCCartMock::getMock(
			'get_subtotal',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);

		$testee   = new CartData( $cart );
		$subTotal = $testee->get_subtotal();

		/**
		 * PayPal handles discounts and fees as Items, so account for that
		 */
		$ppSubtotal = $cart_subtotal - $discount + $this->get_fee_total_from_test_data( $fees );
		$this->assertSame( round( $ppSubtotal ), round( $subTotal ) );
	}

	private function get_fee_total_from_test_data( array $fees ) {

		$sum = 0;
		foreach ( $fees as $fee ) {
			$sum += $fee->amount;
		}

		return $sum;
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_is_total_same_as_item_sum(
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		Functions::expect( 'get_woocommerce_currency' );
		$totalCart   = WCCartMock::getMock(
			'get_total',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax );
		$totalTestee = new CartData( $totalCart );
		$resultTotal = $totalTestee->get_total();

		$itemsCart   = WCCartMock::getMock(
			'get_items',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);
		$itemsTestee = new CartData( $itemsCart );
		$sum         = 0;
		$items       = $itemsTestee->get_items();
		foreach ( $items as $item ) {
			$sum += $item->get_price() * $item->get_quantity();
		}

		$sum         += $shipping;
		$sum         += $tax;
		$sum         = floatval( number_format( $sum, 2, '.', '' ) );
		$resultTotal = floatval( number_format( $resultTotal, 2, '.', '' ) );
		$this->assertSame( $resultTotal, $sum );

	}

	/**
	 *
	 */
	public function default_test_data() {

		$data = [];
		//$data['test_1'] = [
		//	// Cart Items
		//	[
		//		[
		//			'line_subtotal' => 50,
		//			'quantity'      => 1,
		//		],
		//		[
		//			'line_subtotal' => 50,
		//			'quantity'      => 1,
		//		],
		//	],
		//	// Cart total
		//	115.0,
		//	// WooCommerce Subtotal (excluding discounts & fees)
		//	100.0,
		//	// Shipping
		//	10.0,
		//	// Tax
		//	10.0,
		//	// Discount total
		//	10.0,
		//	// Fees
		//	[
		//		(object) [
		//			'name'   => 'foo',
		//			'amount' => 5.0,
		//		],
		//	],
		//	// Prices include tax
		//	true,
		//];
		//
		//$data['test_2'] = [
		//	// Cart Items
		//	[],
		//	// Cart total
		//	0.0,
		//	// WooCommerce Subtotal (excluding discounts & fees)
		//	0.0,
		//	// Shipping
		//	0.0,
		//	// Tax
		//	0.0,
		//	// Discount total
		//	0.0,
		//	// Fees
		//	[],
		//	// Prices include tax
		//	true,
		//];
		//
		//$data['test_3'] = [
		//	// Cart Items
		//	[
		//		[
		//			'line_subtotal' => 70.0,
		//			'quantity'      => 2,
		//		],
		//	],
		//	// Cart total
		//	98.54,
		//	// WooCommerce Subtotal (excluding discounts & fees)
		//	70.0,
		//	// Shipping
		//	4.0,
		//	// Tax
		//	12.54,
		//	// Discount total
		//	8.0,
		//	// Fees
		//	[
		//		(object) [
		//			'name'   => 'foo',
		//			'amount' => 20.0,
		//		],
		//	],
		//	// Prices include tax
		//	true,
		//];

		$data['test_4'] = [
			// Cart Items
			[
				[
					'line_subtotal' => 45.3782,
					'quantity'      => 3,
					'product_id'    => 42,
				],
			],
			// Cart total
			58.76,
			// WooCommerce Subtotal (excluding discounts & fees)
			45.38,
			// Shipping
			4.0,
			// Tax
			9.3818,
			// Discount total
			0.0,
			// Fees
			[],
			// Prices include tax
			true,
		];

		return $data;
	}
}
