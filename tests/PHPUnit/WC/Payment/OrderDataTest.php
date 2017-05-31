<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 30.01.17
 * Time: 15:27
 */

namespace WCPayPalPlus\WC\Payment;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use WCPayPalPlus\Test;

class OrderDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $rawItems
	 * @param           $cart_total
	 * @param           $cart_subtotal
	 * @param           $shipping
	 * @param           $tax
	 * @param           $discount
	 * @param array     $fees
	 */
	public function test_get_total(
		\WC_Order $order,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		Functions::expect( 'WC' )
		         ->andReturn( (object) [ 'version' => '3.0.0' ] );
		$order = Test\WCOrderMock::getMock( 'get_total', $rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);

		$data     = new OrderData( $order );
		$total    = $data->get_total();
		$expected = Test\PriceFormatter::format( $cart_total );
		$this->assertSame( $expected, $total );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $rawItems
	 * @param           $cart_total
	 * @param           $cart_subtotal
	 * @param           $shipping
	 * @param           $tax
	 * @param           $discount
	 * @param array     $fees
	 */
	public function test_get_subtotal(
		\WC_Order $order,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		Functions::expect( 'WC' )
		         ->andReturn( (object) [ 'version' => '3.0.0' ] );
		$order = Test\WCOrderMock::getMock(
			'get_subtotal',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax );

		$testee   = new OrderData( $order );
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
			$sum += $fee['line_total'];
		}

		return $sum;
	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_items(
		\WC_Order $order,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		Functions::expect( 'WC' )
		         ->andReturn( (object) [ 'version' => '3.0.0' ] );
		$order = Test\WCOrderMock::getMock(
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

		$data  = new OrderData( $order );
		$items = $data->get_items();

		$this->assertContainsOnlyInstancesOf( OrderItemDataProvider::class, $items );
		$expectedCount = count( $rawItems );
		$expectedCount += count( $fees );
		$expectedCount += ( $discount > 0 ) ? 1 : 0;

		$this->assertEquals( $expectedCount, count( $items ) );
	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_discount(
		\WC_Order $order,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		$order = Test\WCOrderMock::getMock(
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

		$data   = new OrderData( $order );
		$result = $data->get_total_discount();
		$this->assertSame( $discount, $result );

	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_tax(
		\WC_Order $order,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		Functions::expect( 'get_woocommerce_currency' )
		         ->once();
		$order = Test\WCOrderMock::getMock(
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

		$data     = new OrderData( $order );
		$result   = $data->get_total_tax();
		$expected = Test\PriceFormatter::format( $tax );
		$this->assertSame( $expected, $result );

	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_shipping(
		\WC_Order $order,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {
		Functions::expect( 'get_woocommerce_currency' )
		         ->once();
		$order  = Test\WCOrderMock::getMock(
			'get_shipping_total',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees,
			$pricesIncludeTax
		);
		$data   = new OrderData( $order );
		$result = $data->get_total_shipping();

		$expected = Test\PriceFormatter::format( $shipping );

		$this->assertSame( $expected, $result );

	}

	/**
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			// Cart
			\Mockery::mock( 'WC_Order' ),
			// Cart Items
			[
				[
					'subtotal'   => 50,
					'quantity'   => 1,
					'product_id' => 2,
				],
				[
					'subtotal'   => 50,
					'quantity'   => 1,
					'product_id' => 2,
				],
			],
			// Cart total
			115.0,
			// WooCommerce Subtotal (excluding discounts & fees)
			100.0,
			// Shipping
			10.0,
			// Tax
			10.0,
			// Discount toal
			10.0,
			// Fees
			[
				[
					'name'       => 'foo',
					'line_total' => 5.0,
				],
			],
			// Prices include tax
			true,
		];

		$data['test_2'] = [
			// Cart
			\Mockery::mock( 'WC_Order' ),
			// Cart Items
			[],
			// Cart total
			0.0,
			// WooCommerce Subtotal (excluding discounts & fees)
			0.0,
			// Shipping
			0.0,
			// Tax
			0.0,
			// Discount toal
			0.0,
			// Fees
			[],
			// Prices include tax
			true,
		];

		$data['test_3'] = [
			// Cart
			\Mockery::mock( 'WC_Order' ),
			// Cart Items
			[
				[
					'subtotal'   => 70.0,
					'quantity'   => 2,
					'product_id' => 2,
				],
			],
			// Cart total
			98.54,
			// WooCommerce Subtotal (excluding discounts & fees)
			70.0,
			// Shipping
			4.0,
			// Tax
			12.54,
			// Discount total
			8.0,
			// Fees
			[
				[
					'name'       => 'foo',
					'line_total' => 20.0,
				],
			],
			// Prices include tax
			false,
		];

		$data['test_4_many digits'] = [
			// Cart
			\Mockery::mock( 'WC_Order' ),
			// Cart Items
			[
				[
					'subtotal'   => 70.5789324,
					'quantity'   => 2,
					'product_id' => 2,
				],
			],
			// Cart total
			99.120000000000005,
			// WooCommerce Subtotal (excluding discounts & fees)
			70.5789324,
			// Shipping
			4.0,
			// Tax
			12.54,
			// Discount total
			8.0,
			// Fees
			[
				[
					'name'       => 'foo',
					'line_total' => 20.0,
				],
			],
			// Prices include tax
			false,
		];

		return $data;
	}
}
