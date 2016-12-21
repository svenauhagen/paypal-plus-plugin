<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 16:24
 */

namespace PayPalPlusPlugin\WC\IPN;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

class PaymentValidatorTest extends BrainMonkeyWpTestCase {

	/**
	 * Tests the is_valid method
	 *
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 * @param float     $wc_amount
	 * @param float     $pp_amount
	 * @param string    $transaction_type
	 * @param string[]  $accepted_types
	 * @param string    $wc_currency
	 * @param string    $pp_currency
	 */
	public function test_is_valid( \WC_Order $order, $wc_amount, $pp_amount, $transaction_type, $accepted_types, $wc_currency, $pp_currency ) {

		$order->shouldReceive( 'get_order_currency' )
		      ->once()
		      ->andReturn( $wc_currency );

		$order->shouldReceive( 'get_total' )
		      ->once()
		      ->andReturn( $wc_amount );

		Functions::expect('__');

		$testee = new PaymentValidator( $transaction_type, $pp_currency, $pp_amount, $order, $accepted_types );
		$result = $testee->is_valid();
		if ( in_array( $transaction_type, $accepted_types, TRUE )
		     && $wc_currency === $pp_currency
		     && number_format( $wc_amount, 2, '.', '' ) === number_format( $pp_amount, 2, '.', '' )
		) {
			$this->assertTrue( $result );
		} else {
			$this->assertFalse( $result );

		}

	}

	/**
	 * Provides complete validation test data
	 */
	public function default_test_data() {

		$data = [];

		$data['test_1'] = [
			// Order mock.
			\Mockery::mock( \WC_Order::class ),
			// WooCommerce Price.
			100.00,
			// PayPal price.
			100.00,
			// Transaction type.
			'foo',
			// Accepted Transaction Types.
			[ 'foo', 'bar' ],
			// WooCommerce currency.
			'foo',
			// PayPal currency.
			'foo',
		];

		$data['test_2'] = [
			// Order mock.
			\Mockery::mock( \WC_Order::class ),
			// WooCommerce Price.
			100.00,
			// PayPal price.
			101.00,
			// Transaction type.
			'foo',
			// Accepted Transaction Types.
			[ 'foo', 'bar' ],
			// WooCommerce currency.
			'foo',
			// PayPal currency.
			'foo',
		];

		$data['test_3'] = [
			// Order mock.
			\Mockery::mock( \WC_Order::class ),
			// WooCommerce Price.
			100.00,
			// PayPal price.
			'100.00',
			// Transaction type.
			'foo',
			// Accepted Transaction Types.
			[ 'bar', 'baz' ],
			// WooCommerce currency.
			'foo',
			// PayPal currency.
			'foo',
		];

		$data['test_4'] = [
			// Order mock.
			\Mockery::mock( \WC_Order::class ),
			// WooCommerce Price.
			100.00,
			// PayPal price.
			'101,00',
			// Transaction type.
			'foo',
			// Accepted Transaction Types.
			[ 'foo', 'bar' ],
			// WooCommerce currency.
			'foo',
			// PayPal currency.
			'bar',
		];

		return $data;

	}

}
