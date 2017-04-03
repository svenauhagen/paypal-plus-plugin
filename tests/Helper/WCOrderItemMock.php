<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 03.04.17
 * Time: 12:13
 */

namespace PayPalPlusPlugin\Test;

class WCOrderItemMock {

	/**
	 * @param array $data
	 *
	 * @return \Mockery\MockInterface
	 */
	public static function getMock( array $data ) {

		$mock = \Mockery::mock( \WC_Order_Item::class );
		$mock->shouldReceive( 'get_data' )
		     ->andReturn( $data );

		return $mock;
	}
}