<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 12:18
 */

namespace PayPalPlusPlugin\WC;

use Brain\Monkey\WP\Actions;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use PayPal\Exception\PayPalInvalidCredentialException;
use PayPal\Rest\ApiContext;

class CredentialVerificationTest extends BrainMonkeyWpTestCase {

	/**
	 * @runInSeparateProcess
	 */
	public function test_verify() {

		$saleMock = \Mockery::mock( 'alias:' . 'PayPal\Api\Payment' );
		$saleMock->shouldReceive( 'all' )
		         ->once();

		$context = \Mockery::mock( ApiContext::class );

		$testee = new CredentialVerification( $context );
		$result = $testee->verify();
		$this->assertTrue( $result );

	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_verify_no_credentials() {

		$credEx = \Mockery::mock( PayPalInvalidCredentialException::class, [ 'Foo' ] );

		$payment = \Mockery::mock( 'alias:' . 'PayPal\Api\Payment' );
		$payment->shouldReceive( 'all' )
		        ->once()
		        ->andThrow( $credEx );

		$context = \Mockery::mock( ApiContext::class );

		Actions::expectFired( 'paypal_plus_plugin_log' )
		       ->once()
		       ->with( 'credential_exception', $credEx );

		$testee = new CredentialVerification( $context );
		$result = $testee->verify();
		$this->assertFalse( $result );

		$errors = $testee->get_error_message();
		$this->assertInternalType( 'string', $errors );
		$this->assertNotEmpty( $errors );
	}

}
