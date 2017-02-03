<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 03.02.17
 * Time: 13:31
 */

namespace PayPalPlusPlugin\Test;

use Brain\Monkey\Functions;

class WCOrderMock {

	/**
	 * @param       $context
	 * @param       $rawItems
	 * @param       $cart_total
	 * @param       $cart_subtotal
	 * @param       $shipping
	 * @param       $tax
	 * @param       $discount
	 * @param array $fees
	 *
	 * @return \Mockery\MockInterface|\WC_Order
	 */
	public static function getMock(
		$context,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees
	) {

		$order = \Mockery::mock( 'WC_Order' );
		switch ( $context ) {
			case'get_total':
				$order->shouldReceive( 'get_items' )
				      ->andReturn( $rawItems );

				$order->shouldReceive( 'get_total_discount' )
				      ->once()
				      ->andReturn( $discount );

				$order->shouldReceive( 'get_total_tax' )
				      ->andReturn( $tax );

				$order->shouldReceive( 'get_fees' )
				      ->once()
				      ->andReturn( $fees );

				Functions::expect( 'get_woocommerce_currency' );

				Functions::expect( 'get_option' )
				         ->once()
				         ->andReturn( 'no' );

				$order->shouldReceive( 'get_total_shipping' )
				      ->andReturn( $shipping );
				break;
			case'get_subtotal':
				$order->shouldReceive( 'get_items' )
				      ->andReturn( $rawItems );
				$order->shouldReceive( 'get_total_discount' )
				      ->andReturn( $discount );
				if ( $discount > 0 ) {
					Functions::expect( 'get_woocommerce_currency' );
				}
				$order->shouldReceive( 'get_fees' )
				      ->andReturn( $fees );
				break;
			case'get_items':
				$order->shouldReceive( 'get_items' )
				      ->andReturn( $rawItems );
				$order->shouldReceive( 'get_fees' )
				      ->andReturn( $fees );
				$order->shouldReceive( 'get_total_discount' )
				      ->once()
				      ->andReturn( $discount );
				if ( $discount > 0 ) {
					Functions::expect( 'get_woocommerce_currency' )
					         ->once();
				}
				break;
			case'get_total_discount':
				$order->shouldReceive( 'get_total_discount' )
				      ->andReturn( $discount );
				break;
			case'get_total_tax':
				$order->shouldReceive( 'get_total_tax' )
				      ->andReturn( $tax );
				break;
			case 'get_total_shipping':

				$shippingIncludesTax = (bool) mt_rand( 0, 1 );
				Functions::expect( 'get_option' )
				         ->once()
				         ->andReturn( ( $shippingIncludesTax ) ? 'yes' : 'no' );

				$order->shouldReceive( 'get_total_shipping' )
				      ->andReturn( $shipping );
				if ( $shippingIncludesTax ) {
					$order->shouldReceive( 'get_shipping_tax' )
					      ->andReturn( $tax );
				}

				break;
		}

		return $order;
	}
}