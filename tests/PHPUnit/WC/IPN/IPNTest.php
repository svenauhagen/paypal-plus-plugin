<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 16:33
 */

namespace PayPalPlusPlugin\WC\IPN;

use Brain\Monkey\WP\Actions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

class IPNTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param $gateway_id
	 */
	public function test_register( $gateway_id ) {

		$ipnData = \Mockery::mock( IPNData::class );

		$validator = \Mockery::mock( IPNValidator::class );

		$testee = new IPN( $gateway_id, $ipnData, $validator );

		Actions::expectAdded( 'woocommerce_api_' . $gateway_id . '_ipn' )
		       ->once();

		$result = $testee->register();

		$this->assertTrue( $result );
	}

	/**
	 * @dataProvider updater_test_data
	 *
	 * @param $gateway_id
	 * @param $paymentStatus
	 */
	public function test_valid_response( $gateway_id, $paymentStatus ) {

		$method        = 'payment_status_' . $paymentStatus;
		$shouldReceive = method_exists( OrderUpdater::class, $method );

		$updater = \Mockery::mock( OrderUpdater::class );

		$ipnData = \Mockery::mock( IPNData::class );
		$ipnData->shouldReceive( 'get_payment_status' )
		        ->once()
		        ->andReturn( $paymentStatus );
		$ipnData->shouldReceive( 'get_order_updater' )
		        ->andReturn( $updater );
		if ( $shouldReceive ) {
			$updater->shouldReceive( $method )
			        ->once()
			        ->andReturn( true );
			$ipnData->shouldReceive( 'get_all' )
			        ->once();
			Actions::expectFired( 'paypal_plus_plugin_log' )
			       ->once();
		}

		$validator = \Mockery::mock( IPNValidator::class );
		$testee    = new IPN( $gateway_id, $ipnData, $validator );

		$result = $testee->valid_response();

		$this->assertSame( $result, $shouldReceive );
	}

	/**
	 * @return array
	 */
	public function default_test_data() {

		$data = [];

		# 1. Testrun
		$data['test_1'] = [
			#param $gateway_id
			'foo',
		];

		# 2. Testrun
		$data['test_2'] = [
			#param $gateway_id
			'bar',
		];

		return $data;
	}

	public function updater_test_data() {

		$data = [];

		# 1. Testrun
		$data['test_1'] = [
			#param $gateway_id
			'foo',
			#param $paymentStatus
			'bar',
		];

		# 2. Testrun
		$data['test_2'] = [
			#param $gateway_id
			'bar',
			#param $paymentStatus
			'completed',
		];

		return $data;
	}
}
