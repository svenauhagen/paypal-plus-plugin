<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 09.11.16
 * Time: 12:55
 */

namespace WCPayPalPlus\WC\Payment;

use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use WCPayPalPlus\WC\RequestSuccessHandler;

/**
 * Class WCPaymentExecution
 *
 * @package WCPayPalPlus\WC\Payment
 */
class WCPaymentExecution {

	/**
	 * PaymentExecutionData object.
	 *
	 * @var PaymentExecutionData
	 */
	private $data;
	/**
	 * SuccessHandler object.
	 *
	 * @var RequestSuccessHandler
	 */
	private $success_handlers;

	/**
	 * WCPaymentExecution constructor.
	 *
	 * @param PaymentExecutionData    $data             PaymentExecutionData object.
	 * @param RequestSuccessHandler[] $success_handlers Array of SuccessHandler objects.
	 */
	public function __construct( PaymentExecutionData $data, array $success_handlers ) {

		$this->data             = $data;
		$this->success_handlers = $success_handlers;

		foreach ( $this->success_handlers as $success_handler ) {
			$success_handler->register();
		}
	}

	/**
	 * Execute the Payment.
	 *
	 * @return bool
	 */
	public function execute() {

		try {
			$payment = $this->data->get_payment();
			$payment->execute( $this->data->get_payment_execution(), $this->data->get_context() );
			foreach ( $this->success_handlers as $success_handler ) {
				$success_handler->execute();
			}
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'wc_paypal_plus_log_exception', 'payment_execution_exception', $ex );

			return false;
		}

		return true;
	}

}
