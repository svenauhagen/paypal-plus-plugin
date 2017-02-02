<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.02.17
 * Time: 15:08
 */

namespace PayPalPlusPlugin\WC\Payment;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

class FeeDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $data
	 */
	public function test_get_price( $data ) {

		Functions::expect( 'get_woocommerce_currency' );
		$testee = new FeeData( $data );
		$result = $testee->get_price();
		$this->assertSame( $data->amount, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $data
	 */
	public function test_get_quantity( $data ) {

		$testee = new FeeData( $data );
		$result = $testee->get_quantity();
		$this->assertSame( 1, $result );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param array $data
	 */
	public function test_get_name( $data ) {

		$testee = new FeeData( $data );
		$result = $testee->get_name();
		$this->assertSame( $data->name, $result );

	}

	/**
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			(object) [
				'name'   => 'foo',
				'amount' => 12,
			],
		];

		$data['test_2'] = [
			(object) [
				'name'   => 'bar',
				'amount' => PHP_INT_MAX,
			],
		];

		return $data;
	}
}
