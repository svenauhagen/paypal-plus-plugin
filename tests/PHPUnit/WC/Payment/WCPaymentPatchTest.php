<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 18:31
 */

namespace PayPalPlusPlugin\WC\Payment;

use MonkeryTestCase\BrainMonkeyWpTestCase;
use PayPal\Api\Payment;

class WCPaymentPatchTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param $updateResult
	 */
	public function test_execute( $updateResult ) {

		$payment = \Mockery::mock( Payment::class );
		$payment->shouldReceive( 'update' )
		        ->once()
		        ->andReturn( $updateResult );
		$patchData = \Mockery::mock( PaymentPatchData::class );
		$patchData->shouldReceive( 'get_payment' )
		          ->once()
		          ->andReturn( $payment );
		$patchData->shouldReceive( 'get_patch_request' );
		$patchData->shouldReceive( 'get_api_context' );
		$testee = new WCPaymentPatch( $patchData );
		$result = $testee->execute();
		self::assertSame( $updateResult, $result );
	}

	/**
	 * @return array
	 */
	public function default_test_data() {

		$data = [];

		# 1. Testrun
		$data['test_1'] = [
			#param $updateResult
			TRUE,
		];

		# 2. Testrun
		$data['test_2'] = [
			#param $updateResult
			FALSE,
		];

		return $data;
	}
}
