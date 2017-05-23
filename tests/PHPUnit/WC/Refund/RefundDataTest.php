<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 14:37
 */

namespace WCPayPalPlus\WC\Refund;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use Inpsyde\Lib\PayPal\Api\RefundRequest;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WCPayPalPlus\WC\RequestSuccessHandler;

class RefundDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order  $order
	 * @param            $amount
	 * @param            $reason
	 * @param ApiContext $context
	 */
	public function test_get_amount( \WC_Order $order, $amount, $reason, ApiContext $context ) {

		$testee = new RefundData( $order, $amount, $reason, $context );
		$result = $testee->get_amount();
		$this->assertSame( $result, floatval( $amount ) );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order  $order
	 * @param            $amount
	 * @param            $reason
	 * @param ApiContext $context
	 */
	public function test_get_reason( \WC_Order $order, $amount, $reason, ApiContext $context ) {

		$testee = new RefundData( $order, $amount, $reason, $context );
		$result = $testee->get_reason();
		$this->assertSame( $result, $reason );
	}

	/**
	 * @dataProvider default_test_data
	 * @runInSeparateProcess
	 */
	public function test_get_sale() {

		/**
		 * We need to mock a static method Sale::get() for this test.
		 * To mock it, this test needs to run in a separate process and thus,
		 * we will create our own test data here
		 */
		$order   = \Mockery::mock( 'WC_Order' );
		$amount  = 42;
		$reason  = '';
		$context = \Mockery::mock( ApiContext::class );

		$order->shouldReceive( 'get_transaction_id' )
		      ->once();

		$testee = new RefundData( $order, $amount, $reason, $context );

		$saleMock = \Mockery::mock( 'alias:' . 'PayPal\Api\Sale' );
		$saleMock->shouldReceive( 'get' )
		         ->once()
		         ->andReturn( $saleMock );
		$result = $testee->get_sale();
		$this->assertSame( $result, $saleMock );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order  $order
	 * @param            $amount
	 * @param            $reason
	 * @param ApiContext $context
	 */
	public function test_get_refund( \WC_Order $order, $amount, $reason, ApiContext $context ) {

		$order->shouldReceive( 'get_currency' );
		Functions::expect( 'get_woocommerce_currency' );

		$testee = new RefundData( $order, $amount, $reason, $context );

		$result = $testee->get_refund();

		$this->assertInstanceOf( RefundRequest::class, $result );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order  $order
	 * @param            $amount
	 * @param            $reason
	 * @param ApiContext $context
	 */
	public function test_get_success_handler( \WC_Order $order, $amount, $reason, ApiContext $context ) {

		$transaction_id = 42;

		$testee = new RefundData( $order, $amount, $reason, $context );

		$result = $testee->get_success_handler( $transaction_id );

		$this->assertInstanceOf( RequestSuccessHandler::class, $result );
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
			99.9967,
			'',
			\Mockery::mock( ApiContext::class ),
		];

		$data['test_3'] = [
			\Mockery::mock( 'WC_Order' ),
			"99.9967",
			'',
			\Mockery::mock( ApiContext::class ),
		];

		return $data;
	}
}
