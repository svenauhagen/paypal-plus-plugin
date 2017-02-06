<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 12:18
 */

namespace PayPalPlusPlugin\WC;

use MonkeryTestCase\BrainMonkeyWpTestCase;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class CredentialVerificationTest extends BrainMonkeyWpTestCase {

	/**
	 * @runInSeparateProcess
	 */
	public function test_verify() {

		return;
		$paymentMock = \Mockery::mock( 'alias:' . 'PayPal\Api\Payment' );
		$paymentMock->shouldReceive( 'all' )
		            ->once();

		$credentialMock = \Mockery::mock( OAuthTokenCredential::class );
		$credentialMock->shouldReceive( 'getClientId' )
		               ->andReturn( 'fhlwdjakfhwi' );
		$credentialMock->shouldReceive( 'getClientSecret' )
		               ->andReturn( 'jaajvhsulvsj' );

		$context = \Mockery::mock( ApiContext::class );
		$context->shouldReceive( 'getCredential' )
		        ->once()
		        ->andReturn( $credentialMock );

		$testee = new CredentialVerification( $context );
		$result = $testee->verify();
		$this->assertTrue( $result );

	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_verify_no_credentials() {

		return;
		$credentialMock = \Mockery::mock( OAuthTokenCredential::class );
		$credentialMock->shouldReceive( 'getClientId' )
		               ->andReturn( '' );
		$credentialMock->shouldReceive( 'getClientSecret' )
		               ->andReturn( '' );

		$context = \Mockery::mock( ApiContext::class );
		$context->shouldReceive( 'getCredential' )
		        ->once()
		        ->andReturn( $credentialMock );

		$testee = new CredentialVerification( $context );
		$result = $testee->verify();
		$this->assertFalse( $result );

		$errors = $testee->get_error_message();
		$this->assertInternalType( 'string', $errors );
		$this->assertNotEmpty( $errors );
	}

}
