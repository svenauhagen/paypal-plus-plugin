<?php
namespace PayPalPlusPlugin;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

/**
 * Created by PhpStorm.
 * User: biont
 * Date: 25.10.16
 * Time: 11:21
 */
class PluginTest extends BrainMonkeyWpTestCase {

	/**
	 * @dataProvider default_test_data
	 *
	 */
	public function test_init( $file, $is_admin ) {

		$this->markTestIncomplete( 'Under construction' );

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