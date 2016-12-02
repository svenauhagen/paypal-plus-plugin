<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 28.11.16
 * Time: 13:49
 */

namespace PayPalPlusPlugin\WC\Refund;

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
			} else {
				// Todo: handle this properly
			}
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'paypal-plus-plugin.log', 'refund_exception', $ex );

			return FALSE;
		}

		return TRUE;
	}

}