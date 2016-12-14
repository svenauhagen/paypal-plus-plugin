<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 16:21
 */

namespace PayPalPlusPlugin\WC\IPN;

use MonkeryTestCase\BrainMonkeyWpTestCase;

/**
 * Class OrderUpdaterTest
 *
 * @package PayPalPlusPlugin\WC\IPN
 */
class OrderUpdaterTest extends BrainMonkeyWpTestCase {

	/**
	 * Tests the payment status_completed_method
	 *
	 * @dataProvider default_test_data
	 */
	public function test_payment_status_completed( $order_complete, $validator_result ) {

		$order = \Mockery::mock( \WC_Order::class );
		$order->shouldReceive( 'has_status' )
		      ->once()
		      ->andReturn( $order_complete );

		$data = \Mockery::mock( IPNData::class );

		$validator = \Mockery::mock( PaymentValidator::class );

		if ( ! $order_complete ) {

			$validator->shouldReceive( 'is_valid' )
			          ->once()
			          ->andReturn( $validator_result );

			if ( ! $validator_result ) {

				$order->shouldReceive( 'update_status' )
				      ->once();

				$validator->shouldReceive( 'get_last_error' )
				          ->once()
				          ->andReturn( 'foo' );
			}

			$data->shouldReceive( 'get' )
			     ->withArgs( [ 'txn_type' ] )
			     ->andReturn();

			$data->shouldReceive( 'get' )
			     ->withArgs( [ 'mc_currency' ] )
			     ->andReturn();
		}
		$testee = new OrderUpdater( $order, $data, $validator );
		$result = $testee->payment_status_completed();
		if ( $order_complete ) {
			$this->assertTrue( $result );
		} else {

		}
	}

	/**
	 * Provide test data
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			// Order already complete?.
			TRUE,
			TRUE,
		];

		$data['test_2'] = [
			// Order already complete?.
			FALSE,
			FALSE,
		];

		return $data;

	}
}
