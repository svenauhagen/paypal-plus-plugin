<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 28.11.16
 * Time: 13:49
 */

namespace WCPayPalPlus\WC\Refund;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\PayPal\Rest\ApiContext;

/**
 * Class WCRefund
 *
 * @package WCPayPalPlus\WC\Refund
 */
class WCRefund {

	/**
	 * RefundData object.
	 *
	 * @var ApiContext
	 */
	private $context;
	/**
	 * PayPal Api Context object.
	 *
	 * @var RefundData
	 */
	private $refund_data;

	/**
	 * WCRefund constructor.
	 *
	 * @param RefundData $refund_data RefundData object.
	 * @param ApiContext $context PayPal Api Context object.
	 */
	public function __construct( RefundData $refund_data, ApiContext $context ) {

		$this->context    = $context;
		$this->refund_data = $refund_data;
	}

	/**
	 * Execute the refund via PayPal API
	 *
	 * @return bool
	 */
	public function execute() {

		$sale   = $this->refund_data->get_sale();
		$refund = $this->refund_data->get_refund();

		try {
			$refunded_sale = $sale->refundSale( $refund, $this->context );
			if ( 'completed' === $refunded_sale->state ) {
				$this->refund_data->get_success_handler( $refunded_sale->getId() )
				                  ->execute();
			} else {
				// Todo: handle this properly.
			}
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'wc_paypal_plus_log_exception', 'refund_exception', $ex );

			return false;
		}

		return true;
	}

}
