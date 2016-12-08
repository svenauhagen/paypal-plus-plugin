<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 16:21
 */

namespace PayPalPlusPlugin\WC\IPN;

use MonkeryTestCase\BrainMonkeyWpTestCase;

class OrderUpdaterTest extends BrainMonkeyWpTestCase {

	public function test_payment_status_completed() {

		$this->markTestIncomplete( 'Needs refactoring' );
		$order  = \Mockery::mock( \WC_Order::class );
		$data   = \Mockery::mock( IPNData::class );
		$testee = new OrderUpdater( $order, $data );
		$result = $testee->payment_status_completed();
	}
}
