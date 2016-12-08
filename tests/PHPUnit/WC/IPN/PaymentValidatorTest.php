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
	 * @dataProvider amount_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $wc_amount
	 * @param           $pp_amount
	 */
	public function test_validate_amount( \WC_Order $order, $wc_amount, $pp_amount ) {

		Functions::expect( '__' )
		         ->andReturn( 'error' );

		$order->shouldReceive( 'get_total' )
		      ->once()
		      ->andReturn( $wc_amount );
		$testee = new PaymentValidator( $order );

		$result = $testee->validate_amount( $pp_amount );

		if ( number_format( $wc_amount, 2, '.', '' ) === number_format( $pp_amount, 2, '.', '' ) ) {
			$this->assertTrue( $result );
		} else {
			$this->assertFalse( $result );
			$error = $testee->get_last_error();
			$this->assertInternalType( 'string', $error );
			$this->assertNotEmpty( $error );
		}

	}

	/**
	 * @dataProvider transaction_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $accepted_types
	 * @param           $transaction_type
	 */
	public function test_validate_transaction( \WC_Order $order, $accepted_types, $transaction_type ) {

		Functions::expect( '__' )
		         ->andReturn( 'error' );
		$testee = new PaymentValidator( $order, $accepted_types );

		$result = $testee->validate_transaction_type( $transaction_type );

		if ( in_array( $transaction_type, $accepted_types ) ) {
			$this->assertTrue( $result );

		} else {
			$this->assertFalse( $result );
			$error = $testee->get_last_error();
			$this->assertInternalType( 'string', $error );
			$this->assertNotEmpty( $error );
		}
	}

	public function validate_currency( \WC_Order $order, $wc_currency, $pp_currency ) {

		Functions::expect( '__' )
		         ->andReturn( 'error' );

		$order->shouldReceive( 'get_total' )
		      ->once()
		      ->andReturn( $wc_currency );

		$testee = new PaymentValidator( $order );
		$result = $testee->validate_currency( $pp_currency );
		if ( $wc_currency === $pp_currency ) {
			$this->assertTrue( $result );

		} else {
			$this->assertFalse( $result );
			$error = $testee->get_last_error();
			$this->assertInternalType( 'string', $error );
			$this->assertNotEmpty( $error );
		}

	}

	/**
	 * @return array
	 */
	public function amount_test_data() {

		$data = [];

		$data['test_1'] = [
			\Mockery::mock( \WC_Order::class ),
			100.00,
			100.00,
		];

		$data['test_2'] = [
			\Mockery::mock( \WC_Order::class ),
			"100.00",
			100,
		];

		$data['test_3'] = [
			\Mockery::mock( \WC_Order::class ),
			"100.00",
			200,
		];

		return $data;
	}

	/**
	 * @return array
	 */
	public function transaction_test_data() {

		$data = [];

		$data['test_1'] = [
			\Mockery::mock( \WC_Order::class ),
			[ 'foo', 'bar' ],
			'foo',
		];

		$data['test_2'] = [
			\Mockery::mock( \WC_Order::class ),
			[ 'foo', 'bar' ],
			'baz',
		];

		return $data;
	}

	/**
	 * @return array
	 */
	public function currency_test_data() {

		$data = [];

		$data['test_1'] = [
			\Mockery::mock( \WC_Order::class ),
			'foo',
			'foo',
		];

		$data['test_2'] = [
			\Mockery::mock( \WC_Order::class ),
			'foo',
			'baz',
		];

		return $data;
	}

}
