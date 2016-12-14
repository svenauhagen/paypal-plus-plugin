<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 15:52
 */

namespace PayPalPlusPlugin\WC\IPN;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

/**
 * Class IPNValidatorTest
 *
 * @package PayPalPlusPlugin\WC\IPN
 */
class IPNValidatorTest extends BrainMonkeyWpTestCase {

	/**
	 * Tests the validate method
	 *
	 * @dataProvider default_test_data
	 *
	 * @param array $response Request response dummy.
	 */
	public function test_validate( $response ) {

		$request    = [];
		$url        = '';
		$user_agent = '';

		Functions::expect( 'wp_safe_remote_post' )
		         ->once()
		         ->andReturn( $response );
		$testee = new IPNValidator( $request, $url, $user_agent );
		$result = $testee->validate();

		if ( $response instanceof \WP_Error ) {
			$this->assertFalse( $result );
		} elseif ( ! isset( $response['response']['code'] ) ) {
			$this->assertFalse( $result );
		} else {
			$this->assertTrue( $result );
		}

	}

	/**
	 * Provide test data
	 *
	 * @return array
	 */
	public function default_test_data() {

		$data = [];

		// 1. Testrun.
		$data['test_1'] = [
			// param $response.
			\Mockery::mock( \WP_Error::class ),

		];

		// 2. Testrun.
		$data['test_2'] = [
			// param $response.
			[ 'foo' => 'bar' ],
		];

		// 2. Testrun
		$data['test_2'] = [
			// param $response.
			[
				'response' => [
					'code' => 200,
				],
				'body'     => 'VERIFIED',

			],
		];

		return $data;
	}
}
