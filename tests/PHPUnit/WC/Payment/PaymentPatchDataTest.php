<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 05.12.16
 * Time: 09:55
 */

namespace WCPayPalPlus\WC\Payment;

use MonkeryTestCase\BrainMonkeyWpTestCase;
use Inpsyde\Lib\PayPal\Api\PatchRequest;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

class PaymentPatchDataTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $payment_id
	 * @param           $invoice_prefix
	 * @param           $api_context
	 */
	public function test_get_order( \WC_Order $order, $payment_id, $invoice_prefix, $api_context ) {

		$testee = new PaymentPatchData(
			$order,
			$payment_id,
			$invoice_prefix,
			$api_context
		);

		$result = $testee->get_order();
		$this->assertSame( $order, $result );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $payment_id
	 * @param           $invoice_prefix
	 * @param           $api_context
	 */
	public function test_get_payment_id( \WC_Order $order, $payment_id, $invoice_prefix, $api_context ) {

		$testee = new PaymentPatchData(
			$order,
			$payment_id,
			$invoice_prefix,
			$api_context
		);

		$result = $testee->get_payment_id();
		$this->assertSame( $payment_id, $result );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $payment_id
	 * @param           $invoice_prefix
	 * @param           $api_context
	 */
	public function test_get_invoice_prefix( \WC_Order $order, $payment_id, $invoice_prefix, $api_context ) {

		$testee = new PaymentPatchData(
			$order,
			$payment_id,
			$invoice_prefix,
			$api_context
		);

		$result = $testee->get_invoice_prefix();
		$this->assertSame( $invoice_prefix, $result );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $payment_id
	 * @param           $invoice_prefix
	 * @param           $api_context
	 */
	public function test_get_api_context( \WC_Order $order, $payment_id, $invoice_prefix, $api_context ) {

		$testee = new PaymentPatchData(
			$order,
			$payment_id,
			$invoice_prefix,
			$api_context
		);

		$result = $testee->get_api_context();
		$this->assertSame( $api_context, $result );
	}

	/**
	 * @dataProvider default_test_data
	 * @runInSeparateProcess
	 */
	public function test_get_payment() {

		/**
		 * We need to mock a static method Sale::get() for this test.
		 * To mock it, this test needs to run in a separate process and thus,
		 * we will create our own test data here
		 */
		$order          = \Mockery::mock( 'WC_Order' );
		$payment_id     = 42;
		$invoice_prefix = '';
		$api_context    = \Mockery::mock( ApiContext::class );

		$saleMock = \Mockery::mock( 'alias:' . 'PayPal\Api\Payment' );
		$saleMock->shouldReceive( 'get' )
		         ->once()
		         ->andReturn( $saleMock );
		$testee = new PaymentPatchData(
			$order,
			$payment_id,
			$invoice_prefix,
			$api_context
		);

		$result = $testee->get_payment();
		$this->assertSame( $saleMock, $result );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $payment_id
	 * @param           $invoice_prefix
	 * @param           $api_context
	 */
	public function test_get_patch_request( \WC_Order $order, $payment_id, $invoice_prefix, $api_context ) {

		$patchProvider = \Mockery::mock( PatchProvider::class );

		$patchProvider->shouldReceive( 'get_custom_patch' )
		              ->once();
		$patchProvider->shouldReceive( 'get_invoice_patch' )
		              ->once();
		$patchProvider->shouldReceive( 'get_payment_amount_patch' )
		              ->once();

		$patchProvider->shouldReceive( 'get_billing_patch' )
		              ->once();

		$testee = new PaymentPatchData(
			$order,
			$payment_id,
			$invoice_prefix,
			$api_context,
			$patchProvider
		);

		$result = $testee->get_patch_request();
		$this->assertInstanceOf( PatchRequest::class, $result );
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
