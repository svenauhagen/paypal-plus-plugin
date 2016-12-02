<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 28.11.16
 * Time: 13:49
 */

namespace PayPalPlusPlugin\WC;

use Exception;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class WCRefund {

	/**
	 * @var ApiContext
	 */
	private $context;
	/**
	 * @var RefundData
	 */
	private $factory;

	public function __construct( RefundData $factory, ApiContext $context ) {

		$this->context = $context;
		$this->factory = $factory;
	}

	/**
	 * Execute the refund via PayPal API
	 *
	 * @return bool
	 */
	public function execute() {

		$sale   = $this->factory->get_sale();
		$refund = $this->factory->get_refund();

		try {
			$refundedSale = $sale->refundSale( $refund, $this->context );
			if ( $refundedSale->state == 'completed' ) {
				$this->factory->get_success_handler( $refundedSale->getId() )
				              ->execute();
			}
		} catch ( PayPalConnectionException $ex ) {
			error_log( $ex->getMessage() );
			error_log( $ex->getData() );

			return FALSE;
		} catch ( Exception $ex ) {
			error_log( $ex->getMessage() );

			return FALSE;
		}

		return TRUE;
	}

}