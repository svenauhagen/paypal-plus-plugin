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
		$testee  = new IPNData( [], true );
		$result1 = $testee->get_paypal_url();
		$this->assertInternalType( 'string', $result1 );
		$this->assertNotEmpty( 'string', $result1 );

		$testee  = new IPNData( [], false );
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
	 * @dataProvider paypal_order_data
	 *
	 * @param $request
	 * @param $valid_order_id
	 * @param $valid_order_key
	 */
	public function test_get_paypal_order( $request, $valid_order_id, $valid_order_key ) {

		if ( isset( $request['custom'] ) && ( $custom = json_decode( $request['custom'] ) ) && is_object( $custom ) ) {
			$wc_get_order                 = Functions::expect( 'wc_get_order' );
			$wc_get_order_id_by_order_key = Functions::expect( 'wc_get_order_id_by_order_key' );

			if ( $valid_order_id && ! $valid_order_key ) {

				$wc_get_order->once()
				             ->andReturn( \Mockery::mock( \WC_Order::class ) );
			} elseif ( $valid_order_key && $valid_order_key ) {
				$wc_get_order->once()
				             ->andReturn( \Mockery::mock( \WC_Order::class ) );
			} elseif ( ! $valid_order_id && ! $valid_order_key ) {
				$wc_get_order_id_by_order_key->once();
				$wc_get_order->twice();
				$wc_get_order->andReturn( null );

			} else {// !$valid_order_id && $valid_order_key
				$wc_get_order->once()
				             ->andReturn( null );
				$wc_get_order_id_by_order_key->once()
				                             ->andReturn( 666 );
				$wc_get_order->once()
				             ->andReturn( \Mockery::mock( \WC_Order::class ) );
			}
		}
		$testee = new IPNData( $request );

		$result = $testee->get_paypal_order();

		if ( ! isset( $request['custom'] ) ) {
			$this->assertNull( $result );
		} else {
			if ( $valid_order_id ) {
				$this->assertInstanceOf( \WC_Order::class, $result );
			} else {
				if ( $valid_order_key ) {
					$this->assertInstanceOf( \WC_Order::class, $result );
				} else {
					$this->assertNull( $result );

				}

			}

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
			true,
		];

		# 2. Testrun
		$data['test_2'] = [
			#param $request
			[ 'custom' => 'foo' ],
			#param $sandbox
			false,
		];

		# 3. Testrun
		$data['test_2'] = [
			#param $request
			[ 'custom' => '{"order_id":1337,"order_key":42}' ],
			#param $sandbox
			false,
		];

		return $data;
	}

	public function paypal_order_data() {

		$data = [];

		$data['Missing custom data'] = [
			#param $request
			[ 'foo' => 'bar' ],
			#param $valid_order_id
			false,
			#param $valid_order_key
			false,
		];

		$data['Wrong custom data'] = [
			#param $request
			[ 'custom' => 'foo' ],
			#param $valid_order_id
			false,
			#param $valid_order_key
			false,
		];

		$data['Valid order_id, invalid order_key'] = [
			#param $request
			[ 'custom' => '{"order_id":1337,"order_key":42}' ],
			#param $valid_order_id
			true,
			#param $valid_order_key
			false,
		];

		$data['Valid order_id, valid order_key'] = [
			#param $request
			[ 'custom' => '{"order_id":1337,"order_key":42}' ],
			#param $valid_order_id
			true,
			#param $valid_order_key
			true,
		];

		$data['Invalid order_id, invalid order_key'] = [
			#param $request
			[ 'custom' => '{"order_id":1337,"order_key":42}' ],
			#param $valid_order_id
			false,
			#param $valid_order_key
			false,
		];

		$data['test_2'] = [
			#param $request
			[ 'custom' => '{"order_id":1337,"order_key":42}' ],
			#param $valid_order_id
			true,
			#param $valid_order_key
			true,
		];

		return $data;

	}

}
