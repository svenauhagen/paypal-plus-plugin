<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.02.17
 * Time: 14:34
 */

namespace PayPalPlusPlugin\WC\Payment;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use PayPalPlusPlugin\Test;

/**
 * Class OrderDataCongruenceTest
 *
 * This does not test a specific class, but tests if the behaviour of CartData ond OrderData
 * is the exact same when given the same data
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
class OrderDataCongruenceTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $rawItems
	 * @param       $total
	 * @param       $subTotal
	 * @param       $shipping
	 * @param       $tax
	 * @param       $discount
	 * @param       $fees
	 */
	public function test_total(
		array $rawItems,
		$total,
		$subTotal,
		$shipping,
		$tax,
		$discount,
		$fees
	) {

		$cart = Test\WCCartMock::getMock(
			'get_total',
			$rawItems,
			$total,
			$subTotal,
			$shipping,
			$tax,
			$discount,
			$this->format_fees_for_cart( $fees )
		);

		$order = Test\WCOrderMock::getMock(
			'get_total',
			$rawItems,
			$total,
			$subTotal,
			$shipping,
			$tax,
			$discount,
			$this->format_fees_for_order( $fees )
		);

		$cartData  = new CartData( $cart );
		$orderData = new OrderData( $order );

		$cartTotal  = $cartData->get_total();
		$orderTotal = $orderData->get_total();
		$this->assertSame( $cartTotal, $orderTotal );
	}

	private function format_fees_for_cart( array $fees ) {

		$result = [];
		foreach ( $fees as $fee ) {
			$result[] = (object) $fee;

		}

		return $result;
	}

	private function format_fees_for_order( array $fees ) {

		$result = [];
		foreach ( $fees as $fee ) {
			$result[] = [
				'name'       => $fee['name'],
				'line_total' => $fee['amount'],
			];

		}

		return $result;
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $rawItems
	 * @param       $total
	 * @param       $subTotal
	 * @param       $shipping
	 * @param       $tax
	 * @param       $discount
	 * @param       $fees
	 */
	public function test_get_subtotal(
		array $rawItems,
		$total,
		$subTotal,
		$shipping,
		$tax,
		$discount,
		$fees
	) {

		/**
		 * Setup CartData Mocks
		 */
		$cart = \Mockery::mock( 'WC_Cart' );
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
		     ->andReturn( $this->format_fees_for_cart( $fees ) );

		/**
		 * Setup OrderData Mocks
		 */
		$order = \Mockery::mock( 'WC_Order' );

		$order->shouldReceive( 'get_items' )
		      ->andReturn( $rawItems );
		$order->shouldReceive( 'get_total_discount' )
		      ->andReturn( $discount );
		if ( $discount > 0 ) {
			Functions::expect( 'get_woocommerce_currency' );
		}
		$order->shouldReceive( 'get_fees' )
		      ->andReturn( $this->format_fees_for_order( $fees ) );

		/**
		 * Actual test below
		 */
		$cartData   = new CartData( $cart );
		$orderData  = new OrderData( $order );
		$subTotal   = $cartData->get_subtotal();
		$orderTotal = $orderData->get_subtotal();

		$this->assertSame( $orderTotal, $subTotal );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $rawItems
	 * @param       $total
	 * @param       $subTotal
	 * @param       $shipping
	 * @param       $tax
	 * @param       $discount
	 * @param       $fees
	 */
	public function test_get_items(
		array $rawItems,
		$total,
		$subTotal,
		$shipping,
		$tax,
		$discount,
		$fees
	) {

		$cart = \Mockery::mock( 'WC_Cart' );
		$cart->shouldReceive( 'get_cart' )
		     ->andReturn( $rawItems );

		$cart->shouldReceive( 'get_cart_discount_total' )
		     ->once()
		     ->andReturn( $discount );

		$cart->shouldReceive( 'get_fees' )
		     ->once()
		     ->andReturn( $this->format_fees_for_cart( $fees ) );

		if ( $discount > 0 ) {
			$cart->coupon_discount_amounts['foo'] = $discount;
			$cart->shouldReceive( 'get_coupons' )
			     ->andReturn( [
				     'foo' => 'bar',
			     ] );
			Functions::expect( 'get_woocommerce_currency' )
			         ->once();
		}
		$order = \Mockery::mock( 'WC_Order' );

		$order->shouldReceive( 'get_items' )
		      ->andReturn( $rawItems );
		$order->shouldReceive( 'get_fees' )
		      ->andReturn( $this->format_fees_for_order( $fees ) );
		$order->shouldReceive( 'get_total_discount' )
		      ->once()
		      ->andReturn( $discount );

		if ( $discount > 0 ) {
			Functions::expect( 'get_woocommerce_currency' )
			         ->once();
		}

		$orderData = new OrderData( $order );
		$cartData  = new CartData( $cart );

		$orderItems = $orderData->get_items();
		$cartItems  = $cartData->get_items();
		$this->assertSame( count( $cartItems ), count( $orderItems ) );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $rawItems
	 * @param       $total
	 * @param       $subTotal
	 * @param       $shipping
	 * @param       $tax
	 * @param       $discount
	 * @param       $fees
	 */
	public function test_get_total_tax(
		array $rawItems,
		$total,
		$subTotal,
		$shipping,
		$tax,
		$discount,
		$fees
	) {

		$cart = \Mockery::mock( 'WC_Cart' );

		Functions::expect( 'get_woocommerce_currency' )
		         ->once();
		$cart->shouldReceive( 'get_taxes_total' )
		     ->andReturn( $tax );

		$order = \Mockery::mock( 'WC_Order' );

		$order->shouldReceive( 'get_total_tax' )
		      ->andReturn( $tax );

		$data     = new CartData( $cart );
		$cartTax  = $data->get_total_tax();
		$data     = new OrderData( $order );
		$orderTax = $data->get_total_tax();
		$this->assertSame( $cartTax, $orderTax );
	}

	/**
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			// Cart Items
			[
				[
					'line_subtotal' => 50,
					'quantity'      => 1, // Must be same as qty
					'qty'           => 1, // Must be same as quantity
				],
				[
					'line_subtotal' => 50,
					'quantity'      => 1, // Must be same as qty
					'qty'           => 1, // Must be same as quantity
				],
			],
			// Cart total
			115.0,
			//Subtotal
			95.0,
			// Shipping
			10.0,
			// Tax
			10.0,
			// Discount toal
			10.0,
			// Fees
			[
				[
					'name'   => 'foo',
					'amount' => 5.0,
				],
			],
		];

		return $data;
	}
}