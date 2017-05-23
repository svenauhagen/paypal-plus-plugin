<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 05.12.16
 * Time: 12:24
 */

namespace WCPayPalPlus\WC\Payment;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use Inpsyde\Lib\PayPal\Api\Patch;

class PatchProviderTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 */
	public function test_get_invoice_patch( \WC_Order $order ) {

		$order_id = rand( 0, 42 );
		$order->shouldReceive( 'get_id' )
		      ->andReturn( $order_id );
		$invoicePrefix = 'Testvalue';

		$expected = $invoicePrefix . $order_id;
		$testee   = new PatchProvider( $order );
		$result   = $testee->get_invoice_patch( $invoicePrefix );
		$this->assertInstanceOf( Patch::class, $result );
		$this->assertSame( $expected, $result->getValue() );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 */
	public function test_get_custom_patch( \WC_Order $order ) {

		$order_id  = rand( 0, 999 );
		$order_key = uniqid();
		$order->shouldReceive( 'get_id' )
		      ->andReturn( $order_id );
		$order->shouldReceive( 'get_order_key' )
		      ->andReturn( $order_key );
		Functions::expect( 'wp_json_encode' )
		         ->once()
		         ->andReturnUsing( function ( $data ) {

			         return json_encode( $data );
		         } );
		$testee = new PatchProvider( $order );
		$result = $testee->get_custom_patch();
		$this->assertInstanceOf( Patch::class, $result );
		$data = json_decode( $result->getValue(), true );

		$this->assertArrayHasKey( 'order_id', $data );
		$this->assertSame( $data['order_id'], $order_id );
		$this->assertArrayHasKey( 'order_key', $data );
		$this->assertSame( $data['order_key'], $order_key );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 */
	public function test_get_payment_amount_patch( \WC_Order $order ) {

		$currency      = 'Rai';
		$total         = 200;
		$subtotal      = 100;
		$totalShipping = 50;
		$totalTax      = 50;

		Functions::expect( 'get_woocommerce_currency' )
		         ->andReturn( 'Rai' );
		//$order->shouldReceive( 'get_total' )
		//      ->once()
		//      ->andReturn( $total );
		//$order->shouldReceive( 'get_subtotal' )
		//      ->once()
		//      ->andReturn( $subtotal );
		//$order->shouldReceive( 'get_total_shipping' )
		//      ->once()
		//      ->andReturn( $totalShipping );
		//$order->shouldReceive( 'get_total_tax' )
		//      ->once()
		//      ->andReturn( $totalTax );

		$order_data = \Mockery::mock( OrderData::class );
		//$order_data->shouldReceive( 'get_items' );

		$order_data->shouldReceive( 'get_total' )
		           ->andReturn( $total );

		$order_data->shouldReceive( 'get_subtotal' )
		           ->andReturn( $subtotal );

		$order_data->shouldReceive( 'get_total_shipping' )
		           ->andReturn( $totalShipping );

		$order_data->shouldReceive( 'get_total_tax' )
		           ->andReturn( $totalTax );

		$testee = new PatchProvider( $order, $order_data );
		$result = $testee->get_payment_amount_patch();
		$this->assertInstanceOf( Patch::class, $result );
		$data = $result->getValue();

		$this->assertArrayHasKey( 'total', $data );
		$this->assertArrayHasKey( 'currency', $data );
		$this->assertArrayHasKey( 'details', $data );
		$this->assertArrayHasKey( 'subtotal', $data['details'] );
		$this->assertArrayHasKey( 'shipping', $data['details'] );
		$this->assertArrayHasKey( 'tax', $data['details'] );

		$this->assertSame( $data['total'], $total );
		$this->assertSame( $data['currency'], $currency );
		$this->assertSame( $data['details']['subtotal'], $subtotal );
		$this->assertSame( $data['details']['shipping'], $totalShipping );
		$this->assertSame( $data['details']['tax'], $totalTax );

	}

	/**
	 * @dataProvider shipping_patch_test_data
	 *
	 * @param \WC_Order $order
	 * @param           $billingAddress
	 * @param           $shippingAddress
	 */
	public function test_get_billing_patch( \WC_Order $order, $billingAddress, $shippingAddress ) {

		$hasShipping = ! empty( $shippingAddress['country_code'] );
		$testAddress = ( $hasShipping ) ? $shippingAddress : $billingAddress;

		$order->shouldReceive( 'get_shipping_country' )
		      ->andReturn( $shippingAddress['country_code'] );

		if ( ! $hasShipping ) {
			$order->shouldReceive( 'get_billing_first_name' )
			      ->once()
			      ->andReturn( $testAddress['first_name'] );
			$order->shouldReceive( 'get_billing_last_name' )
			      ->once()
			      ->andReturn( $testAddress['last_name'] );
			$order->shouldReceive( 'get_billing_address_1' )
			      ->once()
			      ->andReturn( $testAddress['address_1'] );
			$order->shouldReceive( 'get_billing_address_2' )
			      ->once()
			      ->andReturn( $testAddress['address_2'] );
			$order->shouldReceive( 'get_billing_city' )
			      ->once()
			      ->andReturn( $testAddress['city'] );
			$order->shouldReceive( 'get_billing_state' )
			      ->once()
			      ->andReturn( $testAddress['state'] );
			$order->shouldReceive( 'get_billing_postcode' )
			      ->once()
			      ->andReturn( $testAddress['postal_code'] );
			$order->shouldReceive( 'get_billing_country' )
			      ->once()
			      ->andReturn( $testAddress['country_code'] );
		} else {
			$order->shouldReceive( 'get_shipping_first_name' )
			      ->once()
			      ->andReturn( $testAddress['first_name'] );
			$order->shouldReceive( 'get_shipping_last_name' )
			      ->once()
			      ->andReturn( $testAddress['last_name'] );
			$order->shouldReceive( 'get_shipping_address_1' )
			      ->once()
			      ->andReturn( $testAddress['address_1'] );
			$order->shouldReceive( 'get_shipping_address_2' )
			      ->once()
			      ->andReturn( $testAddress['address_2'] );
			$order->shouldReceive( 'get_shipping_city' )
			      ->once()
			      ->andReturn( $testAddress['city'] );
			$order->shouldReceive( 'get_shipping_state' )
			      ->once()
			      ->andReturn( $testAddress['state'] );
			$order->shouldReceive( 'get_shipping_postcode' )
			      ->once()
			      ->andReturn( $testAddress['postal_code'] );
			$order->shouldReceive( 'get_shipping_country' )
			      ->once()
			      ->andReturn( $testAddress['country_code'] );
		}

		$testee = new PatchProvider( $order );
		$result = $testee->get_billing_patch();
		$this->assertInstanceOf( Patch::class, $result );
		$data = $result->getValue();

		$this->assertArrayHasKey( 'recipient_name', $data );
		self::assertEquals( $data['recipient_name'], $testAddress['first_name'] . ' ' . $testAddress['last_name'] );

		$this->assertArrayHasKey( 'line1', $data );
		self::assertEquals( $data['line1'], $testAddress['address_1'] );

		$this->assertArrayHasKey( 'line2', $data );
		self::assertEquals( $data['line2'], $testAddress['address_2'] );

		$this->assertArrayHasKey( 'city', $data );
		self::assertEquals( $data['city'], $testAddress['city'] );

		$this->assertArrayHasKey( 'state', $data );
		self::assertEquals( $data['state'], $testAddress['state'] );

		$this->assertArrayHasKey( 'postal_code', $data );
		self::assertEquals( $data['postal_code'], $testAddress['postal_code'] );

		$this->assertArrayHasKey( 'country_code', $data );
		self::assertEquals( $data['country_code'], $testAddress['country_code'] );

	}

	/**
	 * Determine if two associative arrays are similar
	 *
	 * Both arrays must have the same indexes with identical values
	 * without respect to key ordering
	 *
	 * @see http://stackoverflow.com/a/3843768
	 *
	 * @param array $a
	 * @param array $b
	 *
	 * @return bool
	 */
	function arraysAreSimilar( $a, $b ) {

		// if the indexes don't match, return immediately
		if ( count( array_diff_assoc( $a, $b ) ) ) {
			return false;
		}
		// we know that the indexes, but maybe not values, match.
		// compare the values between the two arrays
		foreach ( $a as $k => $v ) {
			if ( $v !== $b[ $k ] ) {
				return false;
			}
		}

		// we have identical indexes, and no unequal values
		return true;
	}

	/**
	 *
	 */
	public function default_test_data() {

		$data           = [];
		$data['test_1'] = [
			\Mockery::mock( 'WC_Order' ),
		];

		return $data;
	}

	public function shipping_patch_test_data() {

		$data = [];

		$data['no_shipping_data'] = [
			\Mockery::mock( 'WC_Order' ),
			// - - - - Billing Fields
			[
				'first_name'   => 'Max',
				'last_name'    => 'Mustermann',
				'address_1'    => 'Musterstraße',
				'address_2'    => '1',
				'city'         => 'Musterstadt',
				'state'        => 'Musterland',
				'postal_code'  => '0815',
				'country_code' => 'DE',
			],
			// - - - - Shipping Fields
			[
				'first_name'   => '',
				'last_name'    => '',
				'address_1'    => '',
				'address_2'    => '',
				'city'         => '',
				'state'        => '',
				'postal_code'  => '',
				'country_code' => '',
			],
		];

		$data['has_shipping_data'] = [
			\Mockery::mock( 'WC_Order' ),

			// - - - - Billing Fields
			[
				'first_name'   => 'Max',
				'last_name'    => 'Mustermann',
				'address_1'    => 'Musterstraße',
				'address_2'    => '1',
				'city'         => 'Musterstadt',
				'state'        => 'Musterland',
				'postal_code'  => '0815',
				'country_code' => 'DE',
			],
			// - - - - Shipping Fields
			[
				'first_name'   => 'SHIPPING Max',
				'last_name'    => 'SHIPPING Mustermann',
				'address_1'    => 'SHIPPING Musterstraße',
				'address_2'    => 'SHIPPING 1',
				'city'         => 'SHIPPING Musterstadt',
				'state'        => 'SHIPPING Musterland',
				'postal_code'  => 'SHIPPING 0815',
				'country_code' => 'SHIPPING DE',
			],
		];

		return $data;
	}
}
