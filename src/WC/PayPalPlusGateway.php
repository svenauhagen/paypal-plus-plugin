<?php
namespace PayPalPlusPlugin\WC;

use Mockery\CountValidator\Exception;
use PayPal\Api\Payment;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use PayPalPlusPlugin\WC\Payment\PaymentData;
use PayPalPlusPlugin\WC\Payment\PaymentPatchData;
use PayPalPlusPlugin\WC\Payment\WCPaymentExecution;
use PayPalPlusPlugin\WC\Payment\WCPaymentPatch;
use PayPalPlusPlugin\WC\Payment\WCPayPalPayment;
use PayPalPlusPlugin\WC\Refund\RefundData;
use PayPalPlusPlugin\WC\Refund\WCRefund;

/**
 * Class PayPalPlusGateway
 *
 * @package PayPalPlusPlugin\WC
 */
class PayPalPlusGateway extends \WC_Payment_Gateway {

	/**
	 * @var string
	 */
	public $id;
	/**
	 * @var string
	 */
	public $method_title;
	/**
	 * @var IPN
	 */
	private $ipn;

	/**
	 * PayPalPlusGateway constructor.
	 *
	 * @param $id
	 * @param $method_title
	 */
	public function __construct( $id, $method_title ) {

		$this->id           = $id;
		$this->title        = $method_title;
		$this->method_title = $method_title;
		$this->has_fields   = TRUE;
		$this->ipn          = new IPN( $this->id, $this->is_sandbox() );
		$this->ipn->register();
		$this->init_form_fields();
		$this->init_settings();
	}

	/**
	 * All hooks and filters are registered here
	 */
	public function register() {

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'on_save' ], 10 );
		add_action( 'woocommerce_receipt_' . $this->id, [ $this, 'render_receipt_page' ] );
		add_action( 'woocommerce_api_' . $this->id, array( $this, 'execute_payment' ), 12 );

	}

	public function execute_payment() {

		if ( ! ( isset( $_GET["token"] ) && ! empty( $_GET["token"] ) && isset( $_GET["PayerID"] ) && ! empty( $_GET["PayerID"] ) ) ) {
			return;
		}
		WC()->session->token = $_GET["token"];
		$payment_id          = WC()->session->paymentId;

		WC()->session->PayerID = $_GET["PayerID"];
		$payment               = new WCPaymentExecution(
			WC()->session->PayerID,
			$payment_id,
			$this->get_api_context() );

		if ( $payment->is_approved() ) {
			$order = new \WC_Order( WC()->session->ppp_order_id );

			$payment->update_order( $order );

			WC()->cart->empty_cart();
			$redirect_url = $order->get_checkout_order_received_url();

		} else {
			wc_add_notice( __( 'Error Payment state:' . $payment->get_payment_state(), 'woo-paypal-plus' ),
				'error' );
			$redirect_url = wc_get_cart_url();
		}
		wp_redirect( $redirect_url );
		exit;
	}

	public function process_refund( $order_id, $amount = NULL, $reason = '' ) {

		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->get_transaction_id() ) {
			return FALSE;
		}
		$refundData = new RefundData( $order, $amount, $reason, $this->get_api_context() );
		$refund     = new WCRefund( $refundData, $this->get_api_context() );

		return $refund->execute();

	}

	public function on_save() {

		// Call regular saving method
		$this->process_admin_options();

		if ( $this->check_api_credentials() ) {
			$option_key = $this->get_experience_profile_option_key();
			$config     = [
				'checkout_logo' => $this->get_option( 'checkout_logo' ),
				'local_id'      => $this->get_option( $option_key ),
				'brand_name'    => $this->get_option( 'brand_name' ),
				'country'       => $this->get_option( 'country' ),
			];

			$web_profile = new WCWebExperienceProfile( $config, $this->get_api_context() );

			$_POST[ $this->get_field_key( $option_key ) ] = $web_profile->save_profile();

		} else {
			$this->add_error( __( 'Your API credentials are either missing or invalid.', 'woo-paypal-plus' ) );
		}

		//Save again to catch all vlues we've updated
		$this->process_admin_options();

	}/** @noinspection PhpInconsistentReturnPointsInspection */

	/**
	 * Generate Settings HTML.
	 *
	 * Generate the HTML for the fields on the "settings" screen.
	 *
	 * @param  array $form_fields (default: array())
	 * @param bool   $echo
	 *
	 * @return string the html for the settings
	 * @since  1.0.0
	 * @uses   method_exists()
	 */
	public function generate_settings_html( $form_fields = array(), $echo = TRUE ) {

		if ( $echo ) {
			$this->display_errors();
			parent::generate_settings_html( $form_fields, $echo );

		} else {
			ob_start();
			$this->display_errors();

			return ob_get_clean() . parent::generate_settings_html( $form_fields, $echo );
		}

	}

	/**
	 * Process the payment
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = new \WC_Order( $order_id );
		if ( isset( WC()->session->token ) ) {
			unset( WC()->session->paymentId );
			unset( WC()->session->PayerID );
		}

		return [
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( TRUE ),
		];
	}

	public function render_receipt_page( $order_id ) {

		WC()->session->ppp_order_id = $order_id;
		$order                      = wc_get_order( $order_id );
		$payment_id                 = WC()->session->paymentId;
		$invoice_prefix             = $this->get_option( 'invoice_prefix' );
		$api_context                = $this->get_api_context();

		$patch_data = new PaymentPatchData(
			$order,
			$payment_id,
			$invoice_prefix,
			$api_context
		);
		$payment    = new WCPaymentPatch( $patch_data );
		if ( $payment->execute() ) {
			$view = new ReceiptPageView();
			$view->render();

		} else {
			wc_add_notice( __( "Error processing checkout. Please try again. ", 'woo-paypal-plus' ), 'error' );
			wp_redirect( wc_get_cart_url() );
		}
	}

	/**
	 * Builds our payment fields area - including tokenization fields for logged
	 * in users, and the actual payment fields.
	 *
	 * @since 2.6.0
	 */
	public function payment_fields() {

		if ( $this->supports( 'tokenization' ) && is_checkout() ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
			$this->form();
			$this->save_payment_method_checkbox();
		} else {
			$this->form();
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = ( new GatewaySettingsModel() )->get_settings();
	}

	public function form() {

		$data = [
			'app_config' => [
				"approvalUrl"      => $this->get_approval_url(),
				"placeholder"      => "ppplus",
				"mode"             => ( $this->is_sandbox() ) ? 'sandbox' : 'live',
				"country"          => $this->get_option( 'country', 'DE' ),
				"language"         => $this->get_locale(),
				'buttonLocation'   => 'outside',
				'showPuiOnSandbox' => TRUE,
			],
		];
		( new PayPalIframeView( $data ) )->render();

	}

	/**
	 *
	 * Make a bogus API call to test if we have corrent API credentials
	 *
	 * @return bool
	 */
	private function check_api_credentials() {

		$api_context = $this->get_api_context();
		if ( is_null( $api_context ) ) {
			return FALSE;
		}
		try {
			$params = array( 'count' => 1 );
			Payment::all( $params, $api_context );
		} catch ( PayPalConnectionException $e ) {
			return FALSE;
		} catch ( Exception $e ) {
			return FALSE;
		}

		return TRUE;
	}

	private function get_locale() {

		$locale = FALSE;
		if ( get_locale() != '' ) {
			$locale = substr( get_locale(), 0, 5 );
		}

		return $locale;
	}

	/**
	 * Creates a new Payment and returns its approval URL
	 *
	 * @return null|string
	 */
	private function get_approval_url() {

		//if ( ! empty( WC()->session->approvalurl ) ) {
		//	return WC()->session->approvalurl;
		//}
		$order = NULL;

		if ( ! empty( $_GET['key'] ) ) {
			$order_key                  = $_GET['key'];
			$order_id                   = wc_get_order_id_by_order_key( $order_key );
			$order                      = new \WC_Order( $order_id );
			WC()->session->ppp_order_id = $order_id;
		}

		$payment                   = ( new WCPayPalPayment( $this->get_payment_data(), $order ) )->create();
		WC()->session->paymentId   = $payment->id;
		WC()->session->approvalurl = isset( $payment->links[1]->href ) ? $payment->links[1]->href : FALSE;

		return $payment->getApprovalLink();
	}

	private function get_payment_data() {

		$return_url     = WC()->api_request_url( $this->id );
		$cancel_url     = wc_get_cart_url();
		$notify_url     = $this->ipn->get_notify_url();
		$web_profile_id = $this->get_option( $this->get_experience_profile_option_key() );
		$api_context    = $this->get_api_context();

		return new PaymentData(
			$return_url,
			$cancel_url,
			$notify_url,
			$web_profile_id,
			$api_context

		);
	}

	/**
	 * Creates a valid PayPal API Context object
	 *
	 * @return ApiContext
	 */
	private function get_api_context() {

		/**
		 * @var $auth ApiContext
		 */
		//static $auth;
		//if ( is_null( $auth ) ) {
		$creds = $this->get_api_credentials();
		if ( empty( $creds['client_id'] ) || empty( $creds['client_secret'] ) ) {
			return NULL;
		}
		$auth = new ApiContext( new OAuthTokenCredential( $creds['client_id'], $creds['client_secret'] ) );
		$auth->setConfig( array(
			'mode'           => ( $this->is_sandbox() ) ? 'SANDBOX' : 'LIVE',
			'log.LogEnabled' => TRUE,
			'log.LogLevel'   => ( $this->is_sandbox() ) ? 'DEBUG' : 'INFO',
			'log.FileName'   => wc_get_log_file_path( 'paypal_plus' ),
			'cache.enabled'  => TRUE,
			'cache.FileName' => wc_get_log_file_path( 'paypal_plus_cache' ),
		) );
		//} else {
		//	$auth->resetRequestId();
		//}

		return $auth;
	}

	/**
	 * Retrieves the API credentials.
	 *
	 * Returns Sandbox credentials if Sandbox mode is active
	 *
	 * @return array
	 */
	private function get_api_credentials() {

		if ( ! $this->is_sandbox() ) {
			$client_key = 'rest_client_id';
			$secret_key = 'rest_secret_id';
		} else {
			$client_key = 'rest_client_id_sandbox';
			$secret_key = 'rest_secret_id_sandbox';
		}

		return [
			'client_id'     => $this->get_option( $client_key ),
			'client_secret' => $this->get_option( $secret_key ),
		];

	}

	/**
	 * Checks if PayPal should be running in sandbox mode
	 *
	 * @return bool
	 */
	private function is_sandbox() {

		return $this->get_option( 'testmode', 'yes' ) === 'yes';

	}

	private function get_experience_profile_option_key() {

		return ( $this->is_sandbox() )
			? 'sandbox_experience_profile_id'
			: 'live_experience_profile_id';

	}

}