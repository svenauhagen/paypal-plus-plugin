<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 03.11.16
 * Time: 17:07
 */

namespace PayPalPlusPlugin\WC;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;

class WCPayPalPayment {

	/**
	 * @var array
	 */
	protected $config;
	/**
	 * @var bool
	 */
	private $is_order;
	/**
	 * @var \WC_Order|null
	 */
	private $order;
	/**
	 * @var Payment
	 */
	private $response;
	/**
	 * @var float
	 */
	private $order_total;
	/**
	 * @var array
	 */
	private $items;

	public function __construct( array $config, \WC_Order $order = NULL ) {

		$this->order       = $order;
		$this->is_order    = ! is_null( $order );
		$this->order_total = ( $this->is_order ) ? $order->get_total() : WC()->cart->total;
		$this->items       = ( $this->is_order ) ? $order->get_items() : WC()->cart->get_cart();
		//TODO check for valid api context
		$this->config = array_merge( [
			'return_url' => home_url(),
			'cancel_url' => wc_get_cart_url(),
			'notify_url' => FALSE,
		], $config );
	}

	/**
	 * @return Payment
	 */
	public function get_payment_object() {

		$payer = new Payer();
		$payer->setPaymentMethod( 'paypal' );
		$item_list = $this->get_item_list();
		$amount    = new Amount();
		$amount->setCurrency( get_woocommerce_currency() )
		       ->setTotal( $this->order_total )
		       ->setDetails( $this->get_details() );

		$transaction = new Transaction();
		$transaction->setAmount( $amount )
		            ->setItemList( $item_list )
		            ->setDescription( 'Payment description' )
		            ->setInvoiceNumber( uniqid() )
		            ->setNotifyUrl( apply_filters( 'angelleye_paypal_plus_ipn_url',
			            WC()->api_request_url( 'Woo_Paypal_Plus_Gateway' ) ) );;

		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl( $this->config['return_url'] )
		             ->setCancelUrl( $this->config['cancel_url'] );

		$payment = new Payment();
		$payment->setIntent( "sale" )
		        ->setExperienceProfileId( $this->config['experience_profile_id'] )
		        ->setPayer( $payer )
		        ->setRedirectUrls( $redirectUrls )
		        ->setTransactions( array( $transaction ) );

		return $payment;

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

		$payment = $this->get_payment_object();

		try {
			$payment->create( $this->config['api_context'] );
		} catch ( PayPalConnectionException $ex ) {
			error_log( $ex->getMessage() );
			error_log( $ex->getData() );
		}

		return $payment;
	}

	/**
	 * Created a Details object for the Paypal API
	 *
	 * @return Details
	 */
	private function get_details() {

		if ( $this->is_order ) {
			if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
				$shipping = $this->order->get_total_shipping() + $this->order->get_shipping_tax();
				$tax      = 0;
			} else {
				$shipping = $this->order->get_total_shipping();
				$tax      = $this->order->get_total_tax();
			}
			if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) && 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
				$tax = $this->order->get_total_tax();
			}
			$sub_total = $this->order->get_subtotal();
		} else {
			if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
				$shipping = WC()->cart->shipping_total + WC()->cart->shipping_tax_total;
				$tax      = 0;
			} else {
				$shipping = WC()->cart->shipping_total;
				$tax      = WC()->cart->get_taxes_total();
			}
			if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) && 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
				$tax = WC()->cart->get_taxes_total();
			}

			$sub_total = WC()->cart->subtotal_ex_tax;
		}

		$details = new Details();
		$details->setShipping( $shipping )
		        ->setTax( $tax )
		        ->setSubtotal( $sub_total );

		return $details;
	}

	/**
	 * Generated a new ItemList object from the items of the current order
	 *
	 * @return ItemList
	 */
	private function get_item_list() {

		$itemList = new ItemList();
		foreach ( $this->items as $order_item ) {

			$itemList->addItem( $this->get_item( $order_item ) );
		}

		return $itemList;
	}

	/**
	 * Creates a single Order Item for the Paypal API
	 *
	 * @param $item
	 *
	 * @return Item
	 */
	private function get_item( $item ) {

		$product  = ( $this->is_order ) ? $this->order->get_product_from_item( $item ) : $item['data'];
		$name     = html_entity_decode( $product->get_title(), ENT_NOQUOTES, 'UTF-8' );
		$currency = get_woocommerce_currency();
		$quantity = ( $this->is_order ) ? absint( $item['qty'] ) : absint( $item['quantity'] );
		$sku      = $product->get_sku();
		$price    = $item['line_subtotal'] / $quantity;

		if ( $product instanceof \WC_Product_Variable ) {
			$sku = $product->parent->get_sku();
		}
		$item = new Item();

		$item->setName( $name )
		     ->setCurrency( $currency )
		     ->setQuantity( $quantity )
		     ->setPrice( $price );

		if ( ! empty( $sku ) ) {
			$item->setSku( $sku );// Similar to `item_number` in Classic API
		}

		return $item;
	}
}