<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 31.01.17
 * Time: 16:13
 */

namespace PayPalPlusPlugin\WC\Payment;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use PayPalPlusPlugin\Test\WCCartMock;

class CartDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Cart $cart
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_total(
		\WC_Cart $cart,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees
	) {

		$cart  = WCCartMock::getMock(
			'get_total',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees );
		$data  = new CartData( $cart );
		$total = $data->get_total();
		$this->assertSame( $cart_total, $total );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Cart $cart
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_items(
		\WC_Cart $cart,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees
	) {

		$cart = WCCartMock::getMock(
			'get_items',
			$rawItems,
			$cart_total,
			$cart_subtotal,
			$shipping,
			$tax,
			$discount,
			$fees
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
	 * @param \WC_Cart $cart
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_total_discount(
		\WC_Cart $cart,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees
	) {

		$cart->shouldReceive( 'get_cart_discount_total' )
		     ->andReturn( $discount );

		$data   = new CartData( $cart );
		$result = $data->get_total_discount();
		$this->assertSame( $discount, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Cart $cart
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_total_tax(
		\WC_Cart $cart,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees
	) {

		Functions::expect( 'get_woocommerce_currency' )
		         ->once();
		$cart->shouldReceive( 'get_taxes_total' )
		     ->andReturn( $tax );

		$data   = new CartData( $cart );
		$result = $data->get_total_tax();
		$this->assertSame( $tax, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Cart $cart
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_total_shipping(
		\WC_Cart $cart,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees
	) {

		$shippingIncludesTax = (bool) mt_rand( 0, 1 );
		Functions::expect( 'get_option' )
		         ->once()
		         ->andReturn( ( $shippingIncludesTax ) ? 'yes' : 'no' );
		$tax = 0;
		if ( $shippingIncludesTax ) {
			$tax                      = mt_rand( 0, 20 );
			$cart->shipping_tax_total = $tax;
		}

		$cart->shipping_total = $shipping;

		$data   = new CartData( $cart );
		$result = $data->get_total_shipping();
		$this->assertSame( $shipping + $tax, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Cart $cart
	 * @param          $rawItems
	 * @param          $cart_total
	 * @param          $cart_subtotal
	 * @param          $shipping
	 * @param          $tax
	 * @param          $discount
	 * @param array    $fees
	 */
	public function test_get_subtotal(
		\WC_Cart $cart,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees
	) {

		$cart->shouldReceive( 'get_cart' )
		     ->andReturn( $rawItems );
		$cart->shouldReceive( 'get_cart_discount_total' )
		     ->andReturn( $discount );
		if ( $discount > 0 ) {
			$cart->coupon_discount_amounts['foo'] = $discount;
			$cart->shouldReceive( 'get_coupons' )
			     ->andReturn( [
				     'foo' => 'bar',
			     ] );
			Functions::expect( 'get_woocommerce_currency' );
		}
		$cart->shouldReceive( 'get_fees' )
		     ->andReturn( $fees );

		$testee   = new CartData( $cart );
		$subTotal = $testee->get_subtotal();

		/**
		 * PayPal handles discounts and fees as Items, so account for that
		 */
		$ppSubtotal = $cart_subtotal - $discount + $this->get_fee_total_from_test_data( $fees );
		$this->assertSame( $ppSubtotal, $subTotal );
	}

	private function get_fee_total_from_test_data( array $fees ) {

		$sum = 0;
		foreach ( $fees as $fee ) {
			$sum += $fee->amount;
		}

		return $sum;
	}

	/**
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			// Cart
			\Mockery::mock( 'WC_Cart' ),
			// Cart Items
			[
				[
					'line_subtotal' => 50,
					'quantity'      => 1,
				],
				[
					'line_subtotal' => 50,
					'quantity'      => 1,
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
				(object) [
					'name'   => 'foo',
					'amount' => 5.0,
				],
			],
		];

		$data['test_2'] = [
			// Cart
			\Mockery::mock( 'WC_Cart' ),
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
		];

		$data['test_3'] = [
			// Cart
			\Mockery::mock( 'WC_Cart' ),
			// Cart Items
			[
				[
					'line_subtotal' => 70.0,
					'quantity'      => 2,
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
			// Discount toal
			8.0,
			// Fees
			[
				(object) [
					'name'   => 'foo',
					'amount' => 20.0,
				],
			],
		];

		return $data;
	}
}
