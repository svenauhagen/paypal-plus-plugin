<?php

namespace WCPayPalPlus\WC;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use WCPayPalPlus\WC\IPN\IPN;
use WCPayPalPlus\WC\IPN\IPNData;
use WCPayPalPlus\WC\Payment\CartData;
use WCPayPalPlus\WC\Payment\OrderData;
use WCPayPalPlus\WC\Payment\OrderDataProvider;
use WCPayPalPlus\WC\Payment\PaymentData;
use WCPayPalPlus\WC\Payment\PaymentExecutionData;
use WCPayPalPlus\WC\Payment\PaymentExecutionSuccess;
use WCPayPalPlus\WC\Payment\PaymentPatchData;
use WCPayPalPlus\WC\Payment\WCPaymentExecution;
use WCPayPalPlus\WC\Payment\WCPaymentPatch;
use WCPayPalPlus\WC\Payment\WCPayPalPayment;
use WCPayPalPlus\WC\PUI\PaymentInstructionRenderer;
use WCPayPalPlus\WC\Refund\RefundData;
use WCPayPalPlus\WC\Refund\WCRefund;

/**
 * Class PayPalPlusGateway
 *
 * @package WCPayPalPlus\WC
 */
class PayPalPlusGateway extends \WC_Payment_Gateway {

	/**
	 * Gateway ID
	 *
	 * @var string
	 */
	public $id;
	/**
	 * Payment Method title.
	 *
	 * @var string
	 */
	public $method_title;
	/**
	 * IPN Handler object.
	 *
	 * @var IPN
	 */
	private $ipn;

	/**
	 * PaymentInstructionRenderer object.
	 *
	 * @var PaymentInstructionRenderer
	 */
	private $pui;

	/**
	 * PayPal API Context object.
	 *
	 * @var ApiContext
	 */
	private $auth;

	/**
	 * PayPalPlusGateway constructor.
	 *
	 * @param string $id           Gateway ID.
	 * @param string $method_title Payment method title.
	 * @param IPN    $ipn          Payment Notification Handler.
	 */
	public function __construct( $id, $method_title, IPN $ipn = null ) {

		$this->id           = $id;
		$this->title        = $method_title;
		$this->method_title = $method_title;
		$this->has_fields   = true;
		$this->supports     = [
			'products',
			'refunds',
		];
		$ipn_data           = new IPNData(
			filter_input_array( INPUT_POST ) ?: [],
			$this->is_sandbox()
		);
		$this->ipn          = $ipn ?: new IPN( $this->id, $ipn_data );
		$this->pui          = new PaymentInstructionRenderer();
		$this->init_form_fields();
		$this->init_settings();
	}

	/**
	 * Checks if PayPal should be running in sandbox mode
	 *
	 * @return bool
	 */
	private function is_sandbox() {

		return $this->get_option( 'testmode', 'yes' ) === 'yes';

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

	/**
	 * All hooks and filters are registered here
	 */
	public function register() {

		$this->ipn->register();
		$this->pui->register();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'on_save' ], 10 );
		add_action( 'woocommerce_receipt_' . $this->id, [ $this, 'render_receipt_page' ] );
		add_action( 'woocommerce_api_' . $this->id, [ $this, 'execute_payment' ], 12 );

		add_action( 'woocommerce_add_to_cart', [ $this, 'clear_session_data' ] );
		add_action( 'woocommerce_cart_item_removed', [ $this, 'clear_session_data' ] );
		add_action( 'woocommerce_after_cart_item_quantity_update', [ $this, 'clear_session_data' ] );

		add_action( 'woocommerce_email_customer_details', [ $this, 'add_legal_note' ], 30, 3 );

	}

	/**
	 * Adds the legal note defined in the settings to the eMail sent to the customer.
	 *
	 * @param \WC_Order $order         The order object.
	 * @param bool      $sent_to_admin Is the eMail sent to admin?.
	 * @param bool      $plain_text    Render plain text?.
	 */
	public function add_legal_note( $order, $sent_to_admin, $plain_text = false ) {

		$instruction_type = get_post_meta( $order->get_id(), 'instruction_type', true );
		if ( ! empty( $instruction_type ) && 'PAY_UPON_INVOICE' === $instruction_type ) {
			if ( ! $sent_to_admin && 'paypal_plus' === $order->get_payment_method() ) {
				if ( $legal_note = $this->get_option( 'legal_note', '' ) ) {
					echo esc_html( wpautop( wptexturize( $legal_note ) ) );
				}
			}
		}
	}

	/**
	 * Carry out a Payment via PayPal API call
	 */
	public function execute_payment() {

		$token    = filter_input( INPUT_GET, 'token' );
		$payer_id = filter_input( INPUT_GET, 'PayerID' );

		if ( ! $token || ! $payer_id ) {
			return;
		}

		WC()->session->token = $token;
		$payment_id          = WC()->session->paymentId;

		WC()->session->PayerID = $payer_id;
		$order                 = new \WC_Order( WC()->session->ppp_order_id );
		$data                  = new PaymentExecutionData(
			$order,
			WC()->session->PayerID,
			$payment_id,
			$this->get_api_context()
		);

		$success = new PaymentExecutionSuccess( $data );
		$payment = new WCPaymentExecution( $data, [ $success ] );
		$payment->execute();

	}

	/**
	 * Creates a valid PayPal API Context object
	 *
	 * @return ApiContext
	 */
	private function get_api_context() {

		if ( is_null( $this->auth ) ) {
			$creds      = $this->get_api_credentials();
			$this->auth = new ApiContext(
				new OAuthTokenCredential(
					$creds['client_id'],
					$creds['client_secret']
				)
			);

			$this->auth->setConfig( [
				'mode'                                       => ( $this->is_sandbox() ) ? 'SANDBOX' : 'LIVE',
				'http.headers.PayPal-Partner-Attribution-Id' => 'WooCommerce_Cart_Plus',
				'log.LogEnabled'                             => true,
				'log.LogLevel'                               => ( $this->is_sandbox() ) ? 'DEBUG' : 'INFO',
				'log.FileName'                               => wc_get_log_file_path( 'paypal_plus' ),
				'cache.enabled'                              => true,
				'cache.FileName'                             => wc_get_log_file_path( 'paypal_plus_cache' ),
			] );
		} else {
			$this->auth->resetRequestId();
		}

		return $this->auth;
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
	 * Carry out a refund via PayPal Api call.
	 *
	 * @param int      $order_id WooCommcerce Order ID.
	 * @param int|null $amount   Refund amount.
	 * @param string   $reason   Reason for refunding.
	 *
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$order = wc_get_order( $order_id );
		if ( ! $this->can_refund_order( $order ) ) {
			return false;
		}
		$refund_data = new RefundData( $order, $amount, $reason, $this->get_api_context() );
		$refund      = new WCRefund( $refund_data, $this->get_api_context() );

		return $refund->execute();

	}

	/**
	 * Can the order be refunded via PayPal?
	 *
	 * @param  \WC_Order $order WooCommerce Order.
	 *
	 * @return bool
	 */
	private function can_refund_order( \WC_Order $order ) {

		return $order && $order->get_transaction_id();
	}

	/**
	 * Admin settings save handler.
	 */
	public function on_save() {

		// Call regular saving method.
		$this->process_admin_options();
		$verification = new CredentialVerification( $this->get_api_context() );
		if ( $verification->verify() ) {
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
			unset( $_POST[ $this->get_field_key( 'enabled' ) ] );
			$this->enabled = 'no';
			$this->add_error(

				sprintf(
					__( 'Your API credentials are either missing or invalid: %s', 'paypalplus-woocommerce' ),
					$verification->get_error_message()
				)
			);
		}

		// Save again to catch all values we've updated.
		$this->process_admin_options();

	}

	/**
	 * Returns the option key where the web experience profile ID is stored
	 *
	 * @return string
	 */
	private function get_experience_profile_option_key() {

		return ( $this->is_sandbox() )
			? 'sandbox_experience_profile_id'
			: 'live_experience_profile_id';

	}

	/**
	 * Generate Settings HTML.
	 *
	 * Generate the HTML for the fields on the "settings" screen.
	 *
	 * @param  array $form_fields (default: array()).
	 * @param  bool  $echo        Optional. Echoes the generated output.
	 *
	 * @return string the html for the settings
	 */
	public function generate_settings_html( $form_fields = [], $echo = true ) {

		ob_start();
		$this->display_errors();

		$output = ob_get_clean();
		$output .= parent::generate_settings_html( $form_fields, $echo );

		if ( $echo ) {
			echo wp_kses_post( $output );

		}

		return $output;
	}

	/**
	 * Process the payment
	 *
	 * @param int $order_id WooCommerce Order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = new \WC_Order( $order_id );
		if ( isset( WC()->session->token ) ) {
			$this->clear_session_data();
		}

		return [
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		];
	}

	/**
	 * Removes all stored session data used by this gateway.
	 */
	public function clear_session_data() {

		unset( WC()->session->paymentId );
		unset( WC()->session->PayerID );
		unset( WC()->session->approvalurl );
	}

	/**
	 * Renders the receipt page.
	 *
	 * @param int $order_id WooCommerce Order ID.
	 */
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
			wc_add_notice( __( 'Error processing checkout. Please try again. ', 'paypalplus-woocommerce' ), 'error' );
			wp_safe_redirect( wc_get_cart_url() );
			exit;
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
	 * Renders the Settings Page.
	 */
	public function form() {

		$data = [
			'app_config' => [
				'useraction'           => 'commit',
				'showLoadingIndicator' => true,
				'approvalUrl'          => $this->get_approval_url(),
				'placeholder'          => 'ppplus',
				'mode'                 => ( $this->is_sandbox() ) ? 'sandbox' : 'live',
				'country'              => WC()->customer->get_country(),
				'language'             => $this->get_locale(),
				'buttonLocation'       => 'outside',
				'showPuiOnSandbox'     => true,
			],
		];
		( new PayPalIframeView( $data ) )->render();

	}

	/**
	 * Creates a new Payment and returns its approval URL
	 *
	 * @return null|string
	 */
	private function get_approval_url() {

		if ( empty( WC()->session->approvalurl ) || empty( WC()->session->paymentId ) ) {

			$order = null;
			$key   = filter_input( INPUT_GET, 'key' );
			if ( $key ) {
				$order_id                   = wc_get_order_id_by_order_key( $key );
				$order                      = new \WC_Order( $order_id );
				WC()->session->ppp_order_id = $order_id;
			}
			$data              = $this->get_payment_data();
			$wc_paypal_payment = new WCPayPalPayment( $data, $this->get_order_data( $order ) );
			$payment           = $wc_paypal_payment->create();
			if ( is_null( $payment ) ) {
				return null;
			}
			WC()->session->paymentId   = $payment->getId();
			WC()->session->approvalurl = $payment->getApprovalLink();
		}

		return WC()->session->approvalurl;
	}

	/**
	 * Returns a configured PaymentData object
	 *
	 * @return PaymentData
	 */
	private function get_payment_data() {

		$return_url     = WC()->api_request_url( $this->id );
		$cancel_url     = wc_get_checkout_url();
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
	 * @param \WC_Order|null $order
	 *
	 * @return OrderDataProvider
	 */
	private function get_order_data( \WC_Order $order = null ) {

		if ( is_null( $order ) ) {
			return new CartData( WC()->cart );
		} else {
			return new OrderData( $order );
		}
	}

	/**
	 * Returns the locale
	 *
	 * @return bool|string
	 */
	private function get_locale() {

		$locale = false;
		if ( get_locale() !== '' ) {
			$locale = substr( get_locale(), 0, 5 );
		}

		return $locale;
	}

}
