<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 01.02.17
 * Time: 08:39
 */

namespace PayPalPlusPlugin\WC\Payment;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

class OrderItemDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $data
	 */
	public function test_get_price( array $data ) {

		Functions::expect( 'get_woocommerce_currency' );
		$testee   = new OrderItemData( $data );
		$expected = number_format( $data['line_subtotal'] / $testee->get_quantity(), 2, '.', '' );
		$result   = $testee->get_price();
		$this->assertSame( $expected, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $data
	 */
	public function test_get_quantity( array $data ) {

		$testee = new OrderItemData( $data );
		$result = $testee->get_quantity();
		$this->assertSame( $data['qty'], $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $data
	 */
	public function test_get_name( array $data ) {

		$name    = 'Lorem ipsum dolor';
		$product = \Mockery::mock( \WC_Product::class );
		$product->shouldReceive( 'get_title' )
		        ->once()
		        ->andReturn( $name );

		Functions::expect( 'wc_get_product' )
		         ->once()
		         ->andReturn( $product );

		$testee = new OrderItemData( $data );
		$result = $testee->get_name();
		$this->assertSame( $result, $name );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $data
	 */
	public function test_get_sku( array $data ) {

		$sku     = 'Lorem ipsum dolor';
		$product = \Mockery::mock( \WC_Product::class );
		$product->shouldReceive( 'get_sku' )
		        ->once()
		        ->andReturn( $sku );

		Functions::expect( 'wc_get_product' )
		         ->once()
		         ->andReturn( $product );

		$testee = new OrderItemData( $data );
		$result = $testee->get_sku();
		$this->assertSame( $result, $sku );

	}

	/**
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			[
				'product_id'    => 42,
				'line_subtotal' => 12,
				'qty'           => 2,
			],
		];

		$data['test_2'] = [
			[
				'product_id'    => '42',
				'line_subtotal' => PHP_INT_MAX,
				'qty'           => 7438657,
			],
		];

		return $data;
	}
}
