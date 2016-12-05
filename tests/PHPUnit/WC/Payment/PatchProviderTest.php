<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 05.12.16
 * Time: 12:24
 */

namespace PayPalPlusPlugin\WC\Payment;

use Brain\Monkey\Functions;
use PayPal\Api\Patch;

class PatchProviderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 */
	public function test_get_invoice_patch( \WC_Order $order ) {

		$order->id     = 42;
		$invoicePrefix = 'Testvalue';

		$expected = $invoicePrefix . $order->id;
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

		$order_id         = rand( 0, 999 );
		$order_key        = uniqid();
		$order->id        = $order_id;
		$order->order_key = $order_key;
		$testee           = new PatchProvider( $order );
		$result           = $testee->get_custom_patch();
		$this->assertInstanceOf( Patch::class, $result );
		$data = json_decode( $result->getValue(), TRUE );

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
		$subttotal     = 100;
		$totalShipping = 50;
		$totalTax      = 50;

		Functions::expect( 'get_woocommerce_currency' )
		         ->andReturn( 'Rai' );
		$order->shouldReceive( 'get_total' )
		      ->once()
		      ->andReturn( $total );
		$order->shouldReceive( 'get_subtotal' )
		      ->once()
		      ->andReturn( $subttotal );
		$order->shouldReceive( 'get_total_shipping' )
		      ->once()
		      ->andReturn( $totalShipping );
		$order->shouldReceive( 'get_total_tax' )
		      ->once()
		      ->andReturn( $totalTax );

		$testee = new PatchProvider( $order );
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
		$this->assertSame( $data['details']['subtotal'], $subttotal );
		$this->assertSame( $data['details']['shipping'], $totalShipping );
		$this->assertSame( $data['details']['tax'], $totalTax );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 */
	public function test_get_billing_patch( \WC_Order $order ) {

		//TODO Maybe write a dataprovider to try different sets of data
		$firstName   = '';
		$lastName    = '';
		$fullName    = $firstName . ' ' . $lastName;
		$adress1     = '';
		$adress2     = '';
		$city        = '';
		$state       = '';
		$postalCode  = '';
		$countryCode = '';

		$order->shipping_first_name = $firstName;
		$order->shipping_last_name  = $lastName;
		$order->shipping_address_1  = $adress1;
		$order->shipping_address_2  = $adress2;
		$order->shipping_city       = $city;
		$order->shipping_state      = $state;
		$order->shipping_postcode   = $postalCode;
		$order->shipping_country    = $countryCode;

		$testee = new PatchProvider( $order );
		$result = $testee->get_billing_patch();
		$this->assertInstanceOf( Patch::class, $result );
		$data = $result->getValue();

		$this->assertArrayHasKey( 'recipient_name', $data );
		self::assertEquals( $data['recipient_name'], $fullName );

		$this->assertArrayHasKey( 'line1', $data );
		self::assertEquals( $data['line1'], $adress1 );

		$this->assertArrayHasKey( 'line2', $data );
		self::assertEquals( $data['line2'], $adress2 );

		$this->assertArrayHasKey( 'city', $data );
		self::assertEquals( $data['city'], $city );

		$this->assertArrayHasKey( 'state', $data );
		self::assertEquals( $data['state'], $state );

		$this->assertArrayHasKey( 'postal_code', $data );
		self::assertEquals( $data['postal_code'], $postalCode );

		$this->assertArrayHasKey( 'country_code', $data );
		self::assertEquals( $data['country_code'], $countryCode );

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param \WC_Order $order
	 */
	public function test_should_patch_billing( \WC_Order $order ) {

		$testee = new PatchProvider( $order );
		if ( $should = rand( 0, 1 ) ) {
			$order->shipping_country = 'Vatican City';
		}
		$result = $testee->should_patch_billing();
		$this->assertInternalType( 'boolean', $result );
		$this->assertEquals( $should, $result );
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
}
