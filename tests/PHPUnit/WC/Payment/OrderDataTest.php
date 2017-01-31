<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 30.01.17
 * Time: 15:27
 */

namespace PayPalPlusPlugin\WC\Payment;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

class OrderDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_items( \WC_Order $order, $rawItems, $discount ) {

		$order = \Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_items' )
		      ->andReturn( $rawItems );

		$order->shouldReceive( 'get_total_discount' )
		      ->once()
		      ->andReturn( $discount );

		if ( $discount > 0 ) {
			Functions::expect( 'get_woocommerce_currency' )
			         ->once();
		}

		$data  = new OrderData( $order );
		$items = $data->get_items();

		$this->assertContainsOnlyInstancesOf( OrderItemDataProvider::class, $items );
		if ( $discount > 0 ) {
			$this->assertEquals( count( $rawItems ) + 1, count( $items ) );
		}
	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_discount( \WC_Order $order, $rawItems, $discount ) {

		$order = \Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_total_discount' )
		      ->andReturn( $discount );

		$data   = new OrderData( $order );
		$result = $data->get_total_discount();
		$this->assertSame( $discount, $result );

	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_tax( \WC_Order $order, $rawItems, $tax ) {

		$order = \Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_total_tax' )
		      ->andReturn( $tax );

		$data   = new OrderData( $order );
		$result = $data->get_total_tax();
		$this->assertSame( $tax, $result );

	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_total_shipping( \WC_Order $order, $rawItems, $shipping ) {

		$shippingIncludesTax = (bool) mt_rand( 0, 1 );
		Functions::expect( 'get_option' )
		         ->once()
		         ->andReturn( ( $shippingIncludesTax ) ? 'yes' : 'no' );

		$order = \Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_total_shipping' )
		      ->andReturn( $shipping );

		if ( $shippingIncludesTax ) {
			$tax = mt_rand( 0, 20 );
			$shipping += $tax;
			$order->shouldReceive( 'get_shipping_tax' )
			      ->andReturn( $shipping );
		}

		$data   = new OrderData( $order );
		$result = $data->get_total_shipping();
		$this->assertSame( $shipping, $result );

	}

	/**
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			\Mockery::mock( 'WC_Order' ),
			[ [], [] ],
			10,
		];

		$data['test_2'] = [
			\Mockery::mock( 'WC_Order' ),
			[],
			0,
		];

		return $data;
	}
}
