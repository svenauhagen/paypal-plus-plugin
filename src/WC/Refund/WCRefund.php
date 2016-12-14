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
	private $refundData;

	/**
	 * WCRefund constructor.
	 *
	 * @param RefundData $refundData
	 * @param ApiContext $context
	 */
	public function __construct( RefundData $refundData, ApiContext $context ) {

		$this->context = $context;
		$this->refundData = $refundData;
	}

	/**
	 * Execute the refund via PayPal API
	 *
	 * @return bool
	 */
	public function execute() {

		$sale   = $this->refundData->get_sale();
		$refund = $this->refundData->get_refund();

		try {
			$refundedSale = $sale->refundSale( $refund, $this->context );
			if ( $refundedSale->state == 'completed' ) {
				$this->refundData->get_success_handler( $refundedSale->getId() )
				                 ->execute();
			} else {
				// Todo: handle this properly
			}
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'paypal_plus_plugin_log', 'refund_exception', $ex );

			return FALSE;
		}

		return TRUE;
	}

}