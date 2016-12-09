<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 08.12.16
 * Time: 11:01
 */

namespace PayPalPlusPlugin\WC\IPN;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

class IPNDataTest extends BrainMonkeyWpTestCase {

	public function test_get_paypal_url() {

		Functions::expect( 'wp_unslash' );
		$testee  = new IPNData( [], TRUE );
		$result1 = $testee->get_paypal_url();
		$this->assertInternalType( 'string', $result1 );
		$this->assertNotEmpty( 'string', $result1 );

		$testee  = new IPNData( [], FALSE );
		$result2 = $testee->get_paypal_url();
		$this->assertInternalType( 'string', $result2 );
		$this->assertNotEmpty( 'string', $result2 );

		$this->assertNotEquals( $result1, $result2 );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param $request
	 * @param $sandbox
	 */
	public function test_get_payment_status( $request, $sandbox ) {

		Functions::expect( 'wp_unslash' );
		$testee = new IPNData( $request, $sandbox );
		$result = $testee->get_payment_status();
		$this->markTestIncomplete( 'See if we even need the added complexity in the test method first' );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param $request
	 * @param $sandbox
	 */
	public function test_get_order_updater( $request, $sandbox ) {

		Functions::expect( 'wp_unslash' );
		$order  = \Mockery::mock( \WC_Order::class );
		$testee = \Mockery::mock( IPNData::class, [ $request, $sandbox ] )
		                  ->makePartial();
		$testee->shouldReceive( 'get_paypal_order' )
		       ->once()
		       ->andReturn( $order );
		$result = $testee->get_order_updater();
		$this->assertInstanceOf( OrderUpdater::class, $result );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param $request
	 * @param $sandbox
	 */
	public function test_get_all( $request, $sandbox ) {

		Functions::expect( 'wp_unslash' );
		$testee = new IPNData( $request, $sandbox );

		$result = $testee->get_all();
		$this->assertEquals( $request, $result );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param $request
	 * @param $sandbox
	 */
	public function test_get_user_agent( $request, $sandbox ) {

		Functions::expect( 'wp_unslash' );
		$wc          = \Mockery::mock( \WooCommerce::class );
		$wc->version = 'foo';
		Functions::expect( 'WC' )
		         ->once()
		         ->andReturn( $wc );

		$testee = new IPNData( $request, $sandbox );

		$result = $testee->get_user_agent();
		$this->assertInternalType( 'string', $result );
		$this->assertNotEmpty( $result );
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param $request
	 * @param $sandbox
	 */
	public function test_get_paypal_order( $request, $sandbox ) {

		$this->markTestIncomplete( 'Have a look at the actual API response first' );

		$testee = new IPNData( $request, $sandbox );

		$result = $testee->get_paypal_order();
		if ( ! isset( $request['custom'] ) ) {
			$this->assertNull( $result );
		} else {
			$this->assertInstanceOf( \WC_Order::class, $result );

		}

	}

	public function test_has() {

		$offset1 = 'foo';
		$offset2 = 'bar';
		$request = [ $offset1 => 'baz' ];

		$testee = new IPNData( $request );

		$result = $testee->has( $offset1 );
		$this->assertTrue( $result );

		$result = $testee->has( $offset2 );
		$this->assertFalse( $result );

	}

	public function test_get() {

		$value1   = 'baz';
		$offset1  = 'foo';
		$offset2  = 'bar';
		$fallback = 1234;
		$request  = [ $offset1 => $value1 ];

		$testee = new IPNData( $request );

		/**
		 * Should pass matching request value
		 */
		$result = $testee->get( $offset1 );
		$this->assertSame( $value1, $result );

		/**
		 * Should return passed fallback if offset not found
		 */
		$result = $testee->get( $offset2, $fallback );
		$this->assertSame( $fallback, $result );

		/**
		 * If no fallback is passed, should return an empty string
		 */
		$result = $testee->get( $offset2 );
		$this->assertSame( '', $result );

	}

	/**
	 * @return array
	 */
	public function default_test_data() {

		$data = [];

		# 1. Testrun
		$data['test_1'] = [
			#param $request
			[ 'payment_status' => 'foo' ],
			#param $sandbox
			TRUE,
		];

		# 2. Testrun
		$data['test_2'] = [
			#param $request
			[ 'custom' => 'foo' ],
			#param $sandbox
			FALSE,
		];

		return $data;
	}

}
