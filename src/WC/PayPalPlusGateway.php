<?php

namespace WCPayPalPlus\WC;

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Auth\OAuthTokenCredential;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WCPayPalPlus\WC\IPN\IPN;
use WCPayPalPlus\WC\IPN\IPNData;
use WCPayPalPlus\WC\Payment\CartData;
use WCPayPalPlus\WC\Payment\OrderData;
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

class PayPalPlusGateway extends \WC_Payment_Gateway
{
    const PAYMENT_ID_SESSION_KEY = 'ppp_payment_id';
    const PAYER_ID_SESSION_KEY = 'ppp_payer_id';
    const APPROVAL_URL_SESSION_KEY = 'ppp_approval_url';

    const CLIENT_ID_KEY = 'woocommerce_paypal_plus_rest_client_id';
    const CLIENT_SECRET_ID_KEY = 'woocommerce_paypal_plus_rest_secret_id';
    const CLIENT_ID_KEY_SANDBOX = self::CLIENT_ID_KEY . '_sandbox';
    const CLIENT_SECRET_ID_KEY_SANDBOX = self::CLIENT_SECRET_ID_KEY . '_sandbox';

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

    public function __construct($id, $methodTitle, IPN $ipn = null)
    {
        $this->id = $id;
        $this->title = $this->get_option('title');
        $this->description = $this->method_description = __(
            'Allow customers to conveniently checkout with different payment options like PayPal, Credit Card or Invoice.',
            'woo-paypalplus'
        );
        $this->method_title = $methodTitle;
        $this->has_fields = true;
        $this->supports = [
            'products',
            'refunds',
        ];
        $ipnData = new IPNData(
            filter_input_array(INPUT_POST) ?: [],
            $this->isSandbox()
        );
        $this->ipn = $ipn ?: new IPN($this->id, $ipnData);
        $this->pui = new PaymentInstructionRenderer($this->get_option('legal_note', ''));
        $this->init_form_fields();
        $this->init_settings();
    }

    public function init_form_fields()
    {
        $this->form_fields = (new GatewaySettingsModel())->settings();
    }

    public function register()
    {
        $this->ipn->register();
        $this->pui->register();

        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            [$this, 'process_admin_options'],
            10
        );
        add_action('woocommerce_receipt_' . $this->id, [$this, 'render_receipt_page']);
        add_action('woocommerce_api_' . $this->id, [$this, 'execute_payment'], 12);
        add_action('woocommerce_add_to_cart', [$this, 'clear_session_data']);
        add_action('woocommerce_cart_item_removed', [$this, 'clear_session_data']);
        add_action('woocommerce_after_cart_item_quantity_update', [$this, 'clear_session_data']);
        add_action('woocommerce_applied_coupon', [$this, 'clear_session_data']);
        add_action('woocommerce_removed_coupon', [$this, 'clear_session_data']);

        if ($this->defaultGatewayOverrideEnabled()) {
            (new DefaultGatewayOverride($this->id))->init();
        }
    }

    public function execute_payment()
    {
        $token = filter_input(INPUT_GET, 'token');
        $payerId = filter_input(INPUT_GET, 'PayerID');
        $paymentId = filter_input(INPUT_GET, 'paymentId');

        if (!$paymentId) {
            $paymentId = WC()->session->__get(self::PAYMENT_ID_SESSION_KEY);
        }

        if (!$token || !$payerId || !$paymentId) {
            return;
        }

        WC()->session->token = $token;

        WC()->session->__set(self::PAYER_ID_SESSION_KEY, $payerId);
        $order = new \WC_Order(WC()->session->ppp_order_id);
        $apiContext = $this->apiContext($this->storedApiCredentials());
        $data = new PaymentExecutionData(
            $order,
            $payerId,
            $paymentId,
            $apiContext
        );

        $success = new PaymentExecutionSuccess($data);

        try {
            $payment = new WCPaymentExecution($data, [$success]);
            $payment->execute();
        } catch (PayPalConnectionException $exc) {
            do_action('wc_paypal_plus_log_exception', 'payment_execution_exception', $exc);

            wc_add_notice(
                __(
                    'Error processing checkout. Please check the logs. ',
                    'woo-paypalplus'
                ),
                'error'
            );

            wp_safe_redirect(wc_get_checkout_url());

            die();
        }
    }

    public function process_refund($orderId, $amount = null, $reason = '')
    {
        $order = wc_get_order($orderId);

        if (!$this->can_refund_order($order)) {
            return false;
        }

        $apiContext = $this->apiContext($this->storedApiCredentials());
        $refundData = new RefundData(
            $order,
            $amount,
            $reason,
            $apiContext
        );

        $refund = new WCRefund($refundData, $apiContext);

        return $refund->execute();
    }

    public function can_refund_order($order)
    {
        return $order && $order->get_transaction_id();
    }

    public function process_admin_options()
    {
        $verification = new CredentialVerification(
            $this->apiContext(
                $this->apiCredentialsByRequest()
            )
        );
        $isValidCredential = $verification->verify();

        switch ($isValidCredential) {
            case true:
                $optionKey = $this->experienceProfileOptionKey();
                $config = [
                    'checkout_logo' => $this->get_option('checkout_logo'),
                    'local_id' => $this->get_option($optionKey),
                    'brand_name' => $this->get_option('brand_name'),
                    'country' => $this->get_option('country'),
                ];
                $apiContext = $this->apiContext($this->storedApiCredentials());
                $webProfile = new WCWebExperienceProfile(
                    $config,
                    $apiContext
                );
                $_POST[$this->get_field_key($optionKey)] = $webProfile->save_profile();
                break;
            case false:
                // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
                unset($_POST[$this->get_field_key('enabled')]);
                $this->enabled = 'no';
                $this->add_error(
                    sprintf(
                        __(
                            'Your API credentials are either missing or invalid: %s',
                            'woo-paypalplus'
                        ),
                        $verification->get_error_message()
                    )
                );
                break;
        }

        $this->data = $this->get_post_data();
        $checkoutLogoUrl = $this->ensureCheckoutLogoUrl(
            $this->data['woocommerce_paypal_plus_checkout_logo']
        );

        if (!$checkoutLogoUrl) {
            return;
        }

        parent::process_admin_options();
    }

    public function generate_settings_html($formFields = [], $echo = true)
    {
        ob_start();
        $this->display_errors();
        $output = ob_get_clean();

        $verification = new CredentialVerification(
            $this->apiContext(
                $this->apiCredentialsByRequest()
            )
        );
        $isValidCredential = $verification->verify();

        $isValidCredential and $this->sandboxMessage($output);
        !$isValidCredential and $this->invalidPaymentMessage($output);

        $output .= parent::generate_settings_html($formFields, $echo);

        if ($echo) {
            echo wp_kses_post($output);
        }

        return $output;
    }

    public function generate_html_html($key, $data)
    {
        $defaults = [
            'title' => '',
            'class' => '',
            'html' => '',
        ];

        $data = wp_parse_args($data, $defaults);

        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php echo wp_kses_post($data['title']); ?>
            </th>
            <td class="forminp <?php echo $data['class'] ?>">
                <?php echo $data['html'] ?>
            </td>
        </tr>
        <?php

        return ob_get_clean();
    }

    public function process_payment($orderId)
    {
        $order = new \WC_Order($orderId);

        return [
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        ];
    }

    public function render_receipt_page($orderId)
    {
        WC()->session->ppp_order_id = $orderId;
        $order = wc_get_order($orderId);
        $paymentId = WC()->session->__get(self::PAYMENT_ID_SESSION_KEY);

        if (!$paymentId) {
            $this->abortCheckout();

            return;
        }

        $invoicePrefix = $this->get_option('invoice_prefix');
        $apiContext = $this->apiContext($this->storedApiCredentials());
        $patchData = new PaymentPatchData(
            $order,
            $paymentId,
            $invoicePrefix,
            $apiContext
        );

        $payment = new WCPaymentPatch($patchData);
        if ($payment->execute()) {
            $view = new ReceiptPageView();
            $view->render();
            return;
        }

        $this->abortCheckout();
    }

    public function clear_session_data()
    {
        $session = WC()->session;
        $session->__unset(self::PAYMENT_ID_SESSION_KEY);
        $session->__unset(self::PAYER_ID_SESSION_KEY);
        $session->__unset(self::APPROVAL_URL_SESSION_KEY);
    }

    public function payment_fields()
    {
        parent::payment_fields();

        if ($this->supports('tokenization') && is_checkout()) {
            $this->tokenization_script();
            $this->saved_payment_methods();
            $this->form();
            $this->save_payment_method_checkbox();
            return;
        }

        $this->form();
    }

    public function form()
    {
        $data = [
            'app_config' => [
                'useraction' => 'commit',
                'showLoadingIndicator' => true,
                'approvalUrl' => $this->approvalUrl(),
                'placeholder' => 'ppplus',
                'mode' => ($this->isSandbox()) ? 'sandbox' : 'live',
                'country' => WC()->customer->get_billing_country(),
                'language' => $this->locale(),
                'buttonLocation' => 'outside',
                'showPuiOnSandbox' => true,
            ],
        ];
        (new PayPalIframeView($data))->render();
    }

    private function ensureCheckoutLogoUrl($checkoutLogoUrl)
    {
        if (strlen($checkoutLogoUrl) > 127) {
            $this->add_error(
                __('Checkout Logo cannot contains more than 127 characters.', 'woo-paypalplus')
            );
            return '';
        }

        if (false === strpos($checkoutLogoUrl, 'https')) {
            $this->add_error(
                __(
                    'Checkout Logo must use the http secure protocol HTTPS. EG. (https://my-url)',
                    'woo-paypalplus'
                )
            );
            return '';
        }

        return $checkoutLogoUrl;
    }

    private function abortCheckout()
    {
        $this->clear_session_data();
        wc_add_notice(
            __('Error processing checkout. Please try again. ', 'woo-paypalplus'),
            'error'
        );

        wp_safe_redirect(wc_get_cart_url());
        exit;
    }

    private function defaultGatewayOverrideEnabled()
    {
        return $this->get_option('disable_gateway_override', 'no') === 'no';
    }

    private function isSandbox()
    {
        return $this->get_option('testmode', 'yes') === 'yes';
    }

    private function apiContext(array $credentials)
    {
        if (empty($credentials['client_id'])
            || empty($credentials['client_secret'])
        ) {
            return null;
        }

        $auth = new ApiContext(
            new OAuthTokenCredential(
                $credentials['client_id'],
                $credentials['client_secret']
            ),
            $this->getRequestID()
        );

        $auth->setConfig([
            'mode' => $this->isSandbox() ? 'SANDBOX' : 'LIVE',
            'http.headers.PayPal-Partner-Attribution-Id' => 'WooCommerce_Cart_Plus',
            'log.LogEnabled' => true,
            'log.LogLevel' => $this->isSandbox() ? 'DEBUG' : 'INFO',
            'log.FileName' => wc_get_log_file_path('paypal_plus'),
            'cache.enabled' => true,
            'cache.FileName' => wc_get_log_file_path('paypal_plus_cache'),
        ]);

        return $auth;
    }

    private function storedApiCredentials()
    {
        $clientKey = 'rest_client_id';
        $secretKey = 'rest_secret_id';

        if ($this->isSandbox()) {
            $clientKey = 'rest_client_id_sandbox';
            $secretKey = 'rest_secret_id_sandbox';
        }

        return [
            'client_id' => $this->get_option($clientKey),
            'client_secret' => $this->get_option($secretKey),
        ];
    }

    private function apiCredentialsByRequest()
    {
        $clientIdKey = $this->isSandbox() ? self::CLIENT_ID_KEY_SANDBOX : self::CLIENT_ID_KEY;
        $clientSecret = $this->isSandbox() ? self::CLIENT_SECRET_ID_KEY_SANDBOX : self::CLIENT_SECRET_ID_KEY;

        $clientId = (string)filter_input(INPUT_POST, $clientIdKey, FILTER_SANITIZE_STRING);
        $clientSecret = (string)filter_input(INPUT_POST, $clientSecret, FILTER_SANITIZE_STRING);

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];
    }

    private function getRequestID()
    {
        return home_url() . uniqid();
    }

    private function experienceProfileOptionKey()
    {
        return ($this->isSandbox())
            ? 'sandbox_experience_profile_id'
            : 'live_experience_profile_id';
    }

    private function approvalUrl()
    {
        $url = WC()->session->__get(self::APPROVAL_URL_SESSION_KEY);

        if (empty($url)) {
            $paymentObject = $this->paymentObject();
            if ($paymentObject === null) {
                return $url;
            }

            $url = $paymentObject->getApprovalLink();
            $url = htmlspecialchars_decode($url);

            WC()->session->__set(
                self::APPROVAL_URL_SESSION_KEY,
                htmlspecialchars_decode($url)
            );
        }

        return $url;
    }

    private function paymentObject()
    {
        static $payment;

        $order = null;
        $key = filter_input(INPUT_GET, 'key');
        $wcSession = WC()->session;
        $id = $wcSession->__get(self::PAYMENT_ID_SESSION_KEY);

        if (!empty($id)) {
            if ($payment !== null && $payment->getId() === $id) {
                return $payment;
            }

            $apiContext = $this->apiContext($this->storedApiCredentials());

            return Payment::get($id, $apiContext);
        }

        if ($key) {
            $order_id = wc_get_order_id_by_order_key($key);
            $order = new \WC_Order($order_id);
            $wcSession->ppp_order_id = $order_id;
        }

        $data = $this->paymentData();
        $wc_paypal_payment = new WCPayPalPayment($data, $this->orderData($order));
        $payment = $wc_paypal_payment->create();

        if ($payment === null) {
            return null;
        }

        $wcSession->__set(self::PAYMENT_ID_SESSION_KEY, $payment->getId());

        return $payment;
    }

    private function paymentData()
    {
        $return_url = WC()->api_request_url($this->id);
        $cancel_url = $this->cancelUrl();
        $notify_url = $this->ipn->get_notify_url();
        $web_profile_id = $this->get_option($this->experienceProfileOptionKey());
        $api_context = $this->apiContext($this->storedApiCredentials());

        return new PaymentData(
            $return_url,
            $cancel_url,
            $notify_url,
            $web_profile_id,
            $api_context
        );
    }

    private function cancelUrl()
    {
        switch ($this->get_option('cancel_url')) {
            case 'cart':
                return wc_get_cart_url();
                break;
            case 'checkout':
                return wc_get_checkout_url();
                break;
            case 'account':
                return wc_get_account_endpoint_url('dashboard');
                break;
            case 'custom':
                return esc_url($this->get_option('cancel_custom_url'));
                break;
            case 'shop':
            default:
                return get_permalink(wc_get_page_id('shop'));
                break;
        }
    }

    private function orderData(\WC_Order $order = null)
    {
        return ($order === null) ? new CartData(WC()->cart) : new OrderData($order);
    }

    private function locale()
    {
        $locale = false;
        if (get_locale() !== '') {
            $locale = substr(get_locale(), 0, 5);
        }

        return $locale;
    }

    private function credentialInformations(&$output, $message)
    {
        $output .= sprintf(
            '<div><p>%s</p></div>',
            esc_html__(
                'Below you can see if your account is successfully hooked up to use PayPal Plus.',
                'woo-paypalplus'
            ) . "<br />{$message}"
        );
    }

    private function invalidPaymentMessage(&$output)
    {
        $this->credentialInformations(
            $output,
            sprintf(
                '<strong class="error-text">%s</strong>',
                esc_html__(
                    'Error connecting to the API. Check that the credentials are correct.',
                    'woo-paypalplus'
                )
            )
        );
    }

    private function sandboxMessage(&$output)
    {
        $msgSandbox = $this->isSandbox()
            ? esc_html__(
                'Note: This is connected to your sandbox account.',
                'woo-paypalplus'
            )
            : esc_html__(
                'Note: This is connected to your live PayPal account.',
                'woo-paypalplus'
            );

        $this->credentialInformations(
            $output,
            sprintf('<strong>%s</strong>', $msgSandbox)
        );
    }
}
