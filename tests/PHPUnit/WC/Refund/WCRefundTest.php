<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 01.12.16
 * Time: 14:22
 */

namespace PayPalPlusPlugin\WC\Refund;

use Brain\Monkey\WP\Actions;
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
	 * @param $refundState
	 */
	public function test_execute( $refundState ) {

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

		$refundSuccess = \Mockery::mock( RefundSuccess::class );
		if ( $refundState === 'completed' ) {
			$refundSuccess->shouldReceive( 'execute' )
			              ->once();
		} else {
			$refundSuccess->shouldNotReceive( 'execute' );

		}
		$factory = \Mockery::mock( RefundData::class );
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
				->andReturn( $refundSuccess );
		}

		$testee = new WCRefund( $factory, $context );
		$result = $testee->execute();
		$this->assertTrue( $result );

	}

	public function test_execute_exception() {

		$context = \Mockery::mock( ApiContext::class );

		$refundRequest = \Mockery::mock( RefundRequest::class );

		$exception = \Mockery::mock( PayPalConnectionException::class );

		$sale = \Mockery::mock( Sale::class );
		$sale->shouldReceive( 'refundSale' )
		     ->andThrow( $exception );

		$factory = \Mockery::mock( RefundData::class );
		$factory->shouldReceive( 'get_sale' )
		        ->andReturn( $sale );
		$factory
			->shouldReceive( 'get_refund' )
			->andReturn( $refundRequest );

		Actions::expectFired( 'paypal_plus_plugin_log' )
		       ->once()
		       ->with( 'refund_exception', $exception );

		$testee = new WCRefund( $factory, $context );
		$result = $testee->execute();

		$this->assertFalse( $result );
	}

	/**
	 * @return array
	 */
	public function default_test_data() {

		$data = [];

		# 1. Testrun
		$data['test_1'] = [
			#param $refundState
			'completed',
		];

		# 2. Testrun
		$data['test_2'] = [
			#param $refundState
			'bogus',
		];

		return $data;
	}
}
