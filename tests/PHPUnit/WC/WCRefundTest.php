<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 01.12.16
 * Time: 14:22
 */

namespace PayPalPlusPlugin\WC;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use PayPal\Api\DetailedRefund;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class WCRefundTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param $amount
	 * @param $reason
	 * @param $refundState
	 */
	public function test_execute( $amount, $reason, $refundState ) {

		$refundId = 42;

		$context = \Mockery::mock( ApiContext::class );

		$refundRequest = \Mockery::mock( RefundRequest::class );

		$refundResult = \Mockery::mock( DetailedRefund::class );
		if ( 'completed' === $refundState ) {
			$refundResult
				->shouldReceive( 'getId' )
				->once()
				->andReturn( $refundId );
		}

		$refundResult->state = $refundState;
		$sale                = \Mockery::mock( Sale::class );
		$sale
			->shouldReceive( 'refundSale' )
			->with( $refundRequest, $context )
			->andReturn( $refundResult );

		$success = \Mockery::mock( RefundSuccess::class );
		if ( $refundState === 'completed' ) {
			$success->shouldReceive( 'execute' )
			        ->once();
		} else {
			$success->shouldNotReceive( 'execute' );

		}
		$factory = \Mockery::mock( RefundData::class )->makePartial();
		$factory->shouldReceive( 'get_sale' )
		        ->andReturn( $sale );
		$factory
			->shouldReceive( 'get_refund' )
			->andReturn( $refundRequest );

		if ( 'completed' === $refundState ) {
			$factory
				->shouldReceive( 'get_success_handler' )
				->once()
				->with( $refundId )
				->andReturn( $success );
		}

		$testee  = new WCRefund( $factory, $context );

		$result = $testee->execute();

		if ( $refundState === 'completed' ) {
			$this->assertTrue( $result );
		} else {
			$this->assertFalse( $result );
		}

	}

	/**
	 * @return array
	 */
	public function default_test_data() {

		$data = [];

		# 1. Testrun
		$data['test_1'] = [
			#param $amount
			20,
			#param $reason
			'For Testing',
			#param $refundState
			'completed',
		];

		# 2. Testrun
		$data['test_2'] = [
			#param $amount
			FALSE,
			#param $reason
			FALSE,
			#param $refundState
			'bogus',
		];

		return $data;
	}
}
