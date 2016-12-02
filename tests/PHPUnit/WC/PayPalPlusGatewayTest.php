<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 01.12.16
 * Time: 12:24
 */

namespace PayPalPlusPlugin\WC;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

class PayPalPlusGatewayTest extends BrainMonkeyWpTestCase {
	/**
	 * @dataProvider default_test_data
	 *
	 */
	public function test_process_refund( $file, $is_admin ) {
		$this->markTestIncomplete( 'Under construction' );

		//Functions::expect( 'is_admin' )
		//         ->once()
		//         ->andReturn( $is_admin );
		////Functions::expect( 'PayPalPlusPlugin\\Plugin\\initialize_common' )
		////         ->once();
		//
		//$gateway = \Mockery::mock( 'overload:' . '\WC_Payment_Gateway' );
		//$gateway->shouldReceive( 'get_option' );
		//
		//$testee = new PayPalPlusGateway('id','method_title');
		//
		//$testee->register();
	}

	/**
	 * @return array
	 */
	public function default_test_data() {

		$data = [];

		# 1. Testrun
		$data['test_1'] = [
			#param $file
			'test_file',
			#param $is_admin
			TRUE,
		];

		# 2. Testrun
		$data['test_2'] = [
			#param $file
			'test_file',
			#param $is_admin
			FALSE,
		];

		return $data;
	}
}
