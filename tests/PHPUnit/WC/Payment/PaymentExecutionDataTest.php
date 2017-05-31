<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 06.12.16
 * Time: 11:10
 */

namespace WCPayPalPlus\WC\Payment;

use MonkeryTestCase\BrainMonkeyWpTestCase;
use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Api\PaymentExecution;
use Inpsyde\Lib\PayPal\Api\PaymentInstruction;
use Inpsyde\Lib\PayPal\Api\RelatedResources;
use Inpsyde\Lib\PayPal\Api\Sale;
use Inpsyde\Lib\PayPal\Api\Transaction;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

class PaymentExecutionDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @runInSeparateProcess
	 *
	 */
	public function test_is_PUI() {

		$hasInstructions = (bool) rand( 0, 1 );
		$instruction     = \Mockery::mock( PaymentInstruction::class );

		$payment = \Mockery::mock( 'alias:' . 'Inpsyde\Lib\PayPal\Api\Payment' );
		$payment->shouldReceive( 'get' )
		        ->once()
		        ->andReturn( $payment );
		$payment->shouldReceive( 'getPaymentInstruction' )
		        ->once()
		        ->andReturn( ( $hasInstructions ) ? $instruction : NULL );

		$order      = \Mockery::mock( 'WC_Order' );
		$payer_id   = 42;
		$payment_id = 'Test';
		$context    = \Mockery::mock( ApiContext::class );

		$testee = new PaymentExecutionData( $order, $payer_id, $payment_id, $context );
		$result = $testee->is_pui();
		$this->assertSame( $hasInstructions, $result );
	}

	/**
	 * @runInSeparateProcess
	 *
	 */
	public function test_is_approved() {

		$isApproved = (bool) rand( 0, 1 );
		$payment    = \Mockery::mock( 'alias:' . 'Inpsyde\Lib\PayPal\Api\Payment' );
		$payment->shouldReceive( 'get' )
		        ->once()
		        ->andReturn( $payment );
		$payment->state = ( $isApproved ) ? 'approved' : 'fubar';

		$order      = \Mockery::mock( 'WC_Order' );
		$payer_id   = 42;
		$payment_id = 'Test';
		$context    = \Mockery::mock( ApiContext::class );

		$testee = new PaymentExecutionData( $order, $payer_id, $payment_id, $context );
		$result = $testee->is_approved();
		$this->assertSame( $isApproved, $result );
	}

	/**
	 * @runInSeparateProcess
	 *
	 */
	public function test_get_payment_instruction() {

		$instruction = \Mockery::mock( PaymentInstruction::class );

		$payment = \Mockery::mock( 'alias:' . 'Inpsyde\Lib\PayPal\Api\Payment' );
		$payment->shouldReceive( 'get' )
		        ->once()
		        ->andReturn( $payment );
		$payment->shouldReceive( 'getPaymentInstruction' )
		        ->once()
		        ->andReturn( $instruction );

		$order      = \Mockery::mock( 'WC_Order' );
		$payer_id   = 42;
		$payment_id = 'Test';
		$context    = \Mockery::mock( ApiContext::class );

		$testee = new PaymentExecutionData( $order, $payer_id, $payment_id, $context );
		$result = $testee->get_payment_instruction();
		$this->assertInstanceOf( PaymentInstruction::class, $result );
	}

	/**
	 * @runInSeparateProcess
	 *
	 */
	public function test_get_sale() {

		$sale            = \Mockery::mock( Sale::class );
		$relatedResource = \Mockery::mock( RelatedResources::class );
		$transaction     = \Mockery::mock( Transaction::class );

		$relatedResources = [ $relatedResource ];

		$transaction->shouldReceive( 'getRelatedResources' )
		            ->once()
		            ->andReturn( $relatedResources );

		$relatedResource->shouldReceive( 'getSale' )
		                ->once()
		                ->andReturn( $sale );
		$transactions = [ $transaction ];

		$payment = \Mockery::mock( 'alias:' . 'Inpsyde\Lib\PayPal\Api\Payment' );
		$payment->shouldReceive( 'get' )
		        ->once()
		        ->andReturn( $payment );
		$payment->shouldReceive( 'getTransactions' )
		        ->once()
		        ->andReturn( $transactions );

		$order      = \Mockery::mock( 'WC_Order' );
		$payer_id   = 42;
		$payment_id = 'Test';
		$context    = \Mockery::mock( ApiContext::class );

		$testee = new PaymentExecutionData( $order, $payer_id, $payment_id, $context );
		$result = $testee->get_sale();
		$this->assertInstanceOf( Sale::class, $result );
	}

	/**
	 * @runInSeparateProcess
	 *
	 */
	public function test_get_payment_state() {

		$state   = 'fubar';
		$payment = \Mockery::mock( 'alias:' . 'Inpsyde\Lib\PayPal\Api\Payment' );
		$payment->shouldReceive( 'get' )
		        ->once()
		        ->andReturn( $payment );
		$payment->state = $state;

		$order      = \Mockery::mock( 'WC_Order' );
		$payer_id   = 42;
		$payment_id = 'Test';
		$context    = \Mockery::mock( ApiContext::class );

		$testee = new PaymentExecutionData( $order, $payer_id, $payment_id, $context );
		$result = $testee->get_payment_state();
		$this->assertSame( $state, $result );

	}

	/**
	 * @runInSeparateProcess
	 *
	 */
	public function test_get_payment() {

		$payment = \Mockery::mock( 'alias:' . 'Inpsyde\Lib\PayPal\Api\Payment' );
		$payment->shouldReceive( 'get' )
		        ->once()
		        ->andReturn( $payment );

		$order      = \Mockery::mock( 'WC_Order' );
		$payer_id   = 42;
		$payment_id = 'Test';
		$context    = \Mockery::mock( ApiContext::class );

		$testee = new PaymentExecutionData( $order, $payer_id, $payment_id, $context );
		$result = $testee->get_payment();
		$this->assertInstanceOf( Payment::class, $result );

	}

	/**
	 * @dataProvider default_test_data
	 */
	public function test_get_payment_execution( $order, $payer_id, $payment_id, $context ) {

		$testee = new PaymentExecutionData( $order, $payer_id, $payment_id, $context );
		$result = $testee->get_payment_execution();
		$this->assertInstanceOf( PaymentExecution::class, $result );

	}

	/**
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			\Mockery::mock( 'WC_Order' ),
			10,
			'For Testing',
			\Mockery::mock( ApiContext::class ),
		];

		$data['test_2'] = [
			\Mockery::mock( 'WC_Order' ),
			'10',
			'',
			\Mockery::mock( ApiContext::class ),
		];

		$data['test_3'] = [
			\Mockery::mock( 'WC_Order' ),
			FALSE,
			'',
			\Mockery::mock( ApiContext::class ),
		];

		return $data;
	}
}
