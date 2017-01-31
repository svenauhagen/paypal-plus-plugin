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

class CartDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_items( \WC_Cart $cart, $rawItems, $discount ) {

		$cart->shouldReceive( 'get_cart' )
		     ->andReturn( $rawItems );

		$cart->shouldReceive( 'get_cart_discount_total' )
		     ->once()
		     ->andReturn( $discount );

		if ( $discount > 0 ) {
			$cart->coupon_discount_amounts['foo'] = $discount;
			$cart->shouldReceive( 'get_coupons' )
			     ->andReturn( [
				     'foo' => 'bar',
			     ] );
			Functions::expect( 'get_woocommerce_currency' )
			         ->once();
		}

		$data  = new CartData( $cart );
		$items = $data->get_items();

		$this->assertContainsOnlyInstancesOf( OrderItemDataProvider::class, $items );
		if ( $discount > 0 ) {
			$this->assertEquals( count( $rawItems ) + 1, count( $items ) );
		}
	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_discount( \WC_Cart $cart, $rawItems, $discount ) {

		$cart->shouldReceive( 'get_cart_discount_total' )
		     ->andReturn( $discount );

		$data   = new CartData( $cart );
		$result = $data->get_total_discount();
		$this->assertSame( $discount, $result );

	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_tax( \WC_Cart $cart, $rawItems, $tax ) {

		$cart->shouldReceive( 'get_taxes_total' )
		     ->andReturn( $tax );

		$data   = new CartData( $cart );
		$result = $data->get_total_tax();
		$this->assertSame( $tax, $result );

	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_shipping( \WC_Cart $cart, $rawItems, $shipping ) {

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
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			\Mockery::mock( 'WC_Cart' ),
			[ [], [] ],
			10,
		];

		$data['test_2'] = [
			\Mockery::mock( 'WC_Cart' ),
			[],
			0,
		];

		return $data;
	}
}
