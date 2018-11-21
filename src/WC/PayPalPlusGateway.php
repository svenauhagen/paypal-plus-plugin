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

    public function __construct($id, $methodTitle, IPN $ipn = null)
    {
        $this->id = $id;
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
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
        $this->form_fields = (new GatewaySettingsModel())->get_settings();
    }

    public function process_refund($orderId, $amount = null, $reason = '')
    {
        $order = wc_get_order($orderId);
        if (!$this->can_refund_order($order)) {
            return false;
        }
        $refundData = new RefundData($order, $amount, $reason, $this->apiContext());
        $refund = new WCRefund($refundData, $this->apiContext());

        return $refund->execute();
    }

    public function can_refund_order($order)
    {
        return $order && $order->get_transaction_id();
    }

    public function generate_settings_html($formFields = [], $echo = true)
    {
        ob_start();
        $this->display_errors();

        $output = ob_get_clean();
        $output .= parent::generate_settings_html($formFields, $echo);

        if (!$echo) {
            return $output;
        }

        echo wp_kses_post($output);
    }

    public function process_payment($orderId)
    {
        $order = new \WC_Order($orderId);

        return [
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        ];
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

    public function register()
    {
        $this->ipn->register();
        $this->pui->register();

        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            [$this, 'on_save'],
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
        $data = new PaymentExecutionData(
            $order,
            $payerId,
            $paymentId,
            $this->apiContext()
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

    public function on_save()
    {
        $this->process_admin_options();
        $verification = new CredentialVerification($this->apiContext());

        if ($verification->verify()) {
            $optionKey = $this->experienceProfileOptionKey();
            $config = [
                'checkout_logo' => $this->get_option('checkout_logo'),
                'local_id' => $this->get_option($optionKey),
                'brand_name' => $this->get_option('brand_name'),
                'country' => $this->get_option('country'),
            ];

            $webProfile = new WCWebExperienceProfile($config, $this->apiContext());
            $_POST[$this->get_field_key($optionKey)] = $webProfile->save_profile();
        } else {
            unset($_POST[$this->get_field_key('enabled')]);
            $this->enabled = 'no';
            $this->add_error(

                sprintf(
                    __('Your API credentials are either missing or invalid: %s', 'woo-paypalplus'),
                    $verification->get_error_message()
                )
            );
        }

        $this->process_admin_options();
    }

    public function generate_html_html($key, $data)
    {
        $field_key = $this->get_field_key($key);
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
        $apiContext = $this->apiContext();
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

    private function apiContext()
    {
        if ($this->auth === null) {
            $creds = $this->apiCredentials();
            $this->auth = new ApiContext(
                new OAuthTokenCredential(
                    $creds['client_id'],
                    $creds['client_secret']
                ),
                $this->getRequestID()
            );

            $this->auth->setConfig([
                'mode' => $this->isSandbox() ? 'SANDBOX' : 'LIVE',
                'http.headers.PayPal-Partner-Attribution-Id' => 'WooCommerce_Cart_Plus',
                'log.LogEnabled' => true,
                'log.LogLevel' => ($this->isSandbox()) ? 'DEBUG' : 'INFO',
                'log.FileName' => wc_get_log_file_path('paypal_plus'),
                'cache.enabled' => true,
                'cache.FileName' => wc_get_log_file_path('paypal_plus_cache'),
            ]);
        } else {
            $this->auth->setRequestId($this->getRequestID());
        }

        return $this->auth;
    }

    private function apiCredentials()
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
        $id = WC()->session->__get(self::PAYMENT_ID_SESSION_KEY);

        if (!empty($id)) {
            if ($payment !== null && $payment->getId() === $id) {
                return $payment;
            }
            return Payment::get($id, $this->apiContext());
        }

        if ($key) {
            $order_id = wc_get_order_id_by_order_key($key);
            $order = new \WC_Order($order_id);
            WC()->session->ppp_order_id = $order_id;
        }

        $data = $this->paymentData();
        $wc_paypal_payment = new WCPayPalPayment($data, $this->orderData($order));
        $payment = $wc_paypal_payment->create();

        if ($payment === null) {
            return null;
        }

        WC()->session->__set(self::PAYMENT_ID_SESSION_KEY, $payment->getId());

        return $payment;
    }

    private function paymentData()
    {
        $return_url = WC()->api_request_url($this->id);
        $cancel_url = $this->cancelUrl();
        $notify_url = $this->ipn->get_notify_url();
        $web_profile_id = $this->get_option($this->experienceProfileOptionKey());
        $api_context = $this->apiContext();

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
}
