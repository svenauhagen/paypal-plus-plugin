<?php
namespace PayPalPlusPlugin\Test;

use Brain\Monkey\Functions;

/**
 * Created by PhpStorm.
 * User: biont
 * Date: 03.02.17
 * Time: 13:26
 */
class WCCartMock {

	/**
	 * @param       $context
	 * @param       $rawItems
	 * @param       $cart_total
	 * @param       $cart_subtotal
	 * @param       $shipping
	 * @param       $tax
	 * @param       $discount
	 * @param array $fees
	 * @param       $pricesIncludeTax
	 *
	 * @return \Mockery\MockInterface|\WC_Cart
	 */
	public static function getMock(
		$context,
		$rawItems,
		$cart_total,
		$cart_subtotal,
		$shipping,
		$tax,
		$discount,
		array $fees,
		$pricesIncludeTax
	) {

		$cart = \Mockery::mock( 'WC_Cart' );
		switch ( $context ) {
			case'get_total':
				$cart->shouldReceive( 'get_cart' )
				     ->andReturn( $rawItems );

				$cart->shouldReceive( 'get_cart_discount_total' )
				     ->once()
				     ->andReturn( $discount );

				$cart->shouldReceive( 'get_taxes_total' )
				     ->andReturn( $tax );

				$cart->shouldReceive( 'get_fees' )
				     ->once()
				     ->andReturn( $fees );

				if ( $discount > 0 ) {
					$cart->coupon_discount_amounts['foo'] = $discount;
					$cart->shouldReceive( 'get_coupons' )
					     ->andReturn( [
						     'foo' => 'bar',
					     ] );
				}
				Functions::expect( 'get_woocommerce_currency' );

				$productMock = \Mockery::mock( 'WC_Product' );
				$productMock->shouldReceive( 'get_title' )
				            ->andReturn( 'foo' );
				$productMock->shouldReceive( 'get_sku' )
				            ->andReturn( 'bar' );
				Functions::expect( 'wc_get_product' )
				         ->andReturn( $productMock );

				//Functions::expect( 'get_option' )
				//         ->once()
				//         ->andReturn( 'no' );

				$cart->shipping_total = $shipping;

				break;
			case'get_subtotal':
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
				}
				Functions::expect( 'get_woocommerce_currency' );

				$cart->shouldReceive( 'get_fees' )
				     ->andReturn( $fees );

				$productMock = \Mockery::mock( 'WC_Product' );
				$productMock->shouldReceive( 'get_title' )
				            ->andReturn( 'foo' );
				$productMock->shouldReceive( 'get_sku' )
				            ->andReturn( 'bar' );
				Functions::expect( 'wc_get_product' )
				         ->andReturn( $productMock );
				break;
			case'get_items':

				$cart->shouldReceive( 'get_cart' )
				     ->andReturn( $rawItems );

				$cart->shouldReceive( 'get_cart_discount_total' )
				     ->once()
				     ->andReturn( $discount );

				$cart->shouldReceive( 'get_fees' )
				     ->once()
				     ->andReturn( $fees );

				if ( $discount > 0 ) {
					$cart->coupon_discount_amounts['foo'] = $discount;
					$cart->shouldReceive( 'get_coupons' )
					     ->andReturn( [
						     'foo' => 'bar',
					     ] );
					Functions::expect( 'get_woocommerce_currency' );
				}
				break;
			case 'get_total_discount':
				$cart->shouldReceive( 'get_cart_discount_total' )
				     ->andReturn( $discount );
				break;
			case 'get_total_tax':
				Functions::expect( 'get_woocommerce_currency' );
				$cart->shouldReceive( 'get_taxes_total' )
				     ->andReturn( $tax );
				break;
			case 'get_total_shipping':
				//Functions::expect( 'get_option' )
				//         ->once()
				//         ->andReturn( ( $pricesIncludeTax ) ? 'yes' : 'no' );
				//if ( $pricesIncludeTax ) {
				//	$cart->shipping_tax_total = $tax;
				//}

				$cart->shipping_total = $shipping;
				break;
		}

		return $cart;
	}
}