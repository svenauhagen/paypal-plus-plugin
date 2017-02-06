<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 03.11.16
 * Time: 17:07
 */

namespace PayPalPlusPlugin\WC\Payment;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;

/**
 * Class WCPayPalPayment
 *
 * @package PayPalPlusPlugin\WC\Payment
 */
class WCPayPalPayment {

	/**
	 * Payment object from response.
	 *
	 * @var Payment
	 */
	private $response;
	/**
	 * The PaymentData object.
	 *
	 * @var PaymentData
	 */
	private $payment_data;
	/**
	 * The Order data provider object.
	 *
	 * @var OrderDataProvider
	 */
	private $order_data;

	/**
	 * WCPayPalPayment constructor.
	 *
	 * @param PaymentData       $data       The PaymentData object.
	 * @param OrderDataProvider $order_data WooCommerce order object.
	 */
	public function __construct( PaymentData $data, OrderDataProvider $order_data ) {

		$this->payment_data = $data;
		$this->order_data   = $order_data;
	}

	/**
	 * Returns the generated Payment object
	 *
	 * @return Payment
	 */
	public function create() {

		if ( is_null( $this->response ) ) {
			$this->response = $this->create_payment();
		}

		return $this->response;
	}

	/**
	 * Creates a new Payment object
	 *
	 * @return Payment
	 */
	private function create_payment() {

		$payment     = $this->get_payment_object();
		$transaction = $payment->getTransactions();
		$itemslist   = $transaction[0]->getItemList();
		$items       = $itemslist->getItems();
		try {
			$payment->create( $this->payment_data->get_api_context() );
		} catch ( PayPalConnectionException $ex ) {
			do_action( 'paypal_plus_plugin_log_exception', 'create_payment_exception', $ex );
			var_dump( $ex );

			return null;
		}

		return $payment;
	}

	/**
	 * Returns a configured Payment object
	 *
	 * @return Payment
	 */
	public function get_payment_object() {

		$payer = new Payer();
		$payer->setPaymentMethod( 'paypal' );
		$item_list = $this->get_item_list();
		$amount    = new Amount();
		$amount->setCurrency( get_woocommerce_currency() )
		       ->setTotal( $this->order_data->get_total() )
		       ->setDetails( $this->get_details() );

		$redirect_urls = new RedirectUrls();
		$redirect_urls->setReturnUrl( $this->payment_data->get_return_url() )
		              ->setCancelUrl( $this->payment_data->get_cancel_url() );

		$payment = new Payment();
		$payment->setIntent( 'sale' )
		        ->setExperienceProfileId( $this->payment_data->get_web_profile_id() )
		        ->setPayer( $payer )
		        ->setRedirectUrls( $redirect_urls )
		        ->setTransactions( [ $this->get_transaction_object( $amount, $item_list ) ] );

		return $payment;

	}

	/**
	 * Generated a new ItemList object from the items of the current order
	 *
	 * @return ItemList
	 */
	private function get_item_list() {

		//$item_list = new ItemList();
		//foreach ( $this->order_data->get_items() as $order_item ) {
		//
		//	$item_list->addItem( $this->order_data->get_item( $order_item ) );
		//}

		return $this->order_data->get_item_list();
	}

	/**
	 * Created a Details object for the Paypal API
	 *
	 * @return Details
	 */
	private function get_details() {

		$shipping  = $this->order_data->get_total_shipping();
		$tax       = $this->order_data->get_total_tax();
		$sub_total = $this->order_data->get_subtotal();

		$details = new Details();
		$details->setShipping( $shipping )
		        ->setTax( $tax )
		        ->setSubtotal( $sub_total );

		return $details;
	}

	/**
	 * Cretae a configured Transaction object.
	 *
	 * @param Amount   $amount    Amount object.
	 * @param ItemList $item_list ItemList object.
	 *
	 * @return Transaction
	 */
	private function get_transaction_object( Amount $amount, ItemList $item_list ) {

		// TODO: receive this from IPN Handler/Data.
		$notify_url = WC()->api_request_url( 'Woo_Paypal_Plus_Gateway' );

		$transaction = new Transaction();
		$transaction->setAmount( $amount )
		            ->setItemList( $item_list )
		            ->setDescription( 'Payment description' )
		            ->setInvoiceNumber( uniqid() )
		            ->setNotifyUrl( $notify_url );

		return $transaction;

	}
}
