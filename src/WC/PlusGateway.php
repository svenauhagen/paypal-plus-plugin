<?php

namespace WCPayPalPlus\WC;

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Auth\OAuthTokenCredential;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use const WCPayPalPlus\ACTION_LOG;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Notice;
use WCPayPalPlus\Ipn\Ipn;
use WCPayPalPlus\Setting\PlusRepositoryHelper;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\WC\Payment\CartData;
use WCPayPalPlus\WC\Payment\OrderData;
use WCPayPalPlus\WC\Payment\PaymentData;
use WCPayPalPlus\WC\Payment\PaymentExecutionData;
use WCPayPalPlus\WC\Payment\PaymentExecutionSuccess;
use WCPayPalPlus\WC\Payment\PaymentPatchData;
use WCPayPalPlus\WC\Payment\WCPaymentExecution;
use WCPayPalPlus\WC\Payment\WCPaymentPatch;
use WCPayPalPlus\WC\Payment\WCPayPalPayment;
use WCPayPalPlus\WC\Refund\RefundData;
use WCPayPalPlus\WC\Refund\WCRefund;

/**
 * Class PayPalPlusGateway
 * @package WCPayPalPlus\WC
 */
class PlusGateway extends \WC_Payment_Gateway implements PlusStorable
{
    use PlusRepositoryHelper;

    const GATEWAY_ID = 'paypal_plus';
    const GATEWAY_TITLE_METHOD = 'PayPal PLUS';

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
     *
     * phpcs:disable Inpsyde.CodeQuality.ForbiddenPublicProperty.Found
     */
    public $id;
    // phpcs:enable

    /**
     * Payment Method title.
     *
     * @var string
     *
     * phpcs:disable Inpsyde.CodeQuality.ForbiddenPublicProperty.Found
     */
    public $method_title;
    // phpcs:enable

    /**
     * @var PlusGateway
     */
    private $gateway;

    /**
     * @var PlusFrameView
     */
    private $frameView;

    /**
     * PlusGateway constructor.
     * @param PlusFrameView $frameView
     */
    public function __construct(PlusFrameView $frameView)
    {
        $this->gateway = $this;
        $this->frameView = $frameView;
        $this->id = self::GATEWAY_ID;
        $this->title = $this->get_option('title');
        $this->method_title = self::GATEWAY_TITLE_METHOD;
        $this->description = $this->get_option('description');
        $this->method_description = _x(
            'Allow customers to conveniently checkout with different payment options like PayPal, Direct Debit, Credit Card and Invoice (if available).',
            'gateway-settings',
            'woo-paypalplus'
        );

        $this->has_fields = true;
        $this->supports = [
            'products',
            'refunds',
        ];

        $this->init_form_fields();
        $this->init_settings();
    }

    /**
     * @inheritdoc
     */
    public function init_form_fields()
    {
        $this->form_fields = (new GatewaySettingsModel())->settings();
    }

    /**
     * @param int $orderId
     * @param null $amount
     * @param string $reason
     * @return bool
     */
    public function process_refund($orderId, $amount = null, $reason = '')
    {
        $order = wc_get_order($orderId);

        if (!$this->can_refund_order($order)) {
            return false;
        }

        $apiContext = ApiContextFactory::get();
        $refundData = new RefundData(
            $order,
            $amount,
            $reason,
            $apiContext
        );

        $refund = new WCRefund($refundData, $apiContext);

        return $refund->execute();
    }

    /**
     * @param \WC_Order $order
     * @return bool
     */
    public function can_refund_order($order)
    {
        return $order && $order->get_transaction_id();
    }

    /**
     * @return bool|void
     */
    public function process_admin_options()
    {
        $apiContext = ApiContextFactory::get($this->apiCredentialsByRequest());
        $verification = new CredentialVerification($apiContext);
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

    /**
     * @param array $formFields
     * @param bool $echo
     * @return false|string
     */
    public function generate_settings_html($formFields = [], $echo = true)
    {
        ob_start();
        $this->display_errors();
        do_action(Notice\Admin::ACTION_ADMIN_MESSAGES);
        $output = ob_get_clean();

        $verification = new CredentialVerification(
            ApiContextFactory::get()
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

    /**
     * @param int $orderId
     * @return array
     */
    public function process_payment($orderId)
    {
        $order = new \WC_Order($orderId);

        return [
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        ];
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function form()
    {
        $data = [
            'useraction' => 'commit',
            'showLoadingIndicator' => true,
            'approvalUrl' => $this->approvalUrl(),
            'placeholder' => 'ppplus',
            'mode' => $this->isSandboxed() ? 'sandbox' : 'live',
            'country' => WC()->customer->get_billing_country(),
            'language' => $this->locale(),
            'buttonLocation' => 'outside',
            'showPuiOnSandbox' => true,
        ];

        $this->frameView->render($data);
    }

    /**
     * @param $orderId
     */
    public function render_receipt_page($orderId)
    {
        wc()->session->ppp_order_id = $orderId;
        $order = wc_get_order($orderId);
        $paymentId = wc()->session->__get(self::PAYMENT_ID_SESSION_KEY);

        if (!$paymentId) {
            $this->abortCheckout();

            return;
        }

        $invoicePrefix = $this->get_option('invoice_prefix');
        $patchData = new PaymentPatchData(
            $order,
            $paymentId,
            $invoicePrefix,
            ApiContextFactory::get()
        );

        $payment = new WCPaymentPatch($patchData);
        if ($payment->execute()) {
            wp_enqueue_script('paypalplus-woocommerce-plus-paypal-redirect');
            return;
        }

        $this->abortCheckout();
    }

    /**
     * @return void
     */
    public function clear_session_data()
    {
        $session = wc()->session;
        $session->__unset(self::PAYMENT_ID_SESSION_KEY);
        $session->__unset(self::PAYER_ID_SESSION_KEY);
        $session->__unset(self::APPROVAL_URL_SESSION_KEY);
    }

    /**
     * @param $key
     * @param $data
     * @return false|string
     */
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

    /**
     * @return void
     */
    public function execute_payment()
    {
        $token = filter_input(INPUT_GET, 'token');
        $payerId = filter_input(INPUT_GET, 'PayerID');
        $paymentId = filter_input(INPUT_GET, 'paymentId');

        if (!$paymentId) {
            $paymentId = wc()->session->__get(self::PAYMENT_ID_SESSION_KEY);
        }

        if (!$token || !$payerId || !$paymentId) {
            return;
        }

        wc()->session->token = $token;

        wc()->session->__set(self::PAYER_ID_SESSION_KEY, $payerId);
        $order = new \WC_Order(wc()->session->ppp_order_id);
        $data = new PaymentExecutionData(
            $order,
            $payerId,
            $paymentId,
            ApiContextFactory::get()
        );

        $success = new PaymentExecutionSuccess($data);

        try {
            $payment = new WCPaymentExecution($data, [$success]);
            $payment->execute();
        } catch (PayPalConnectionException $exc) {
            do_action(ACTION_LOG, \WC_Log_Levels::ERROR, 'payment_execution_exception: ' . $exc->getMessage(), compact($exc));

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

    /**
     * @param $checkoutLogoUrl
     * @return string
     */
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

    /**
     * @return void
     */
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

    /**
     * @return OAuthTokenCredential
     */
    private function apiCredentialsByRequest()
    {
        $clientIdKey = $this->isSandboxed() ? self::CLIENT_ID_KEY_SANDBOX : self::CLIENT_ID_KEY;
        $clientSecret = $this->isSandboxed() ? self::CLIENT_SECRET_ID_KEY_SANDBOX : self::CLIENT_SECRET_ID_KEY;

        $clientId = (string)filter_input(INPUT_POST, $clientIdKey, FILTER_SANITIZE_STRING);
        $clientSecret = (string)filter_input(INPUT_POST, $clientSecret, FILTER_SANITIZE_STRING);

        return new OAuthTokenCredential($clientId, $clientSecret);
    }

    /**
     * @return string
     */
    private function experienceProfileOptionKey()
    {
        return ($this->isSandboxed())
            ? 'sandbox_experience_profile_id'
            : 'live_experience_profile_id';
    }

    /**
     * @return mixed|string|null
     */
    private function approvalUrl()
    {
        $url = wc()->session->__get(self::APPROVAL_URL_SESSION_KEY);

        if (empty($url)) {
            $paymentObject = $this->paymentObject();
            if ($paymentObject === null) {
                return $url;
            }

            $url = $paymentObject->getApprovalLink();
            $url = htmlspecialchars_decode($url);

            wc()->session->__set(
                self::APPROVAL_URL_SESSION_KEY,
                htmlspecialchars_decode($url)
            );
        }

        return $url;
    }

    /**
     * @return Payment|null
     */
    private function paymentObject()
    {
        static $payment;

        $order = null;
        $key = filter_input(INPUT_GET, 'key');
        $wcSession = wc()->session;
        $id = $wcSession->__get(self::PAYMENT_ID_SESSION_KEY);

        if (!empty($id)) {
            if ($payment !== null && $payment->getId() === $id) {
                return $payment;
            }

            return Payment::get($id, ApiContextFactory::get());
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

    /**
     * @return PaymentData
     */
    private function paymentData()
    {
        $return_url = wc()->api_request_url($this->id);
        $cancel_url = $this->cancelUrl();
        $notify_url = wc()->api_request_url(self::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX);
        $web_profile_id = $this->get_option($this->experienceProfileOptionKey());

        return new PaymentData(
            $return_url,
            $cancel_url,
            $notify_url,
            $web_profile_id,
            ApiContextFactory::get()
        );
    }

    /**
     * @return false|string
     */
    private function cancelUrl()
    {
        // phpcs:disable Squiz.PHP.NonExecutableCode.Unreachable
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
        // phpcs:enable
    }

    /**
     * @param \WC_Order|null $order
     * @return CartData|OrderData
     */
    private function orderData(\WC_Order $order = null)
    {
        return ($order === null) ? new CartData(wc()->cart) : new OrderData($order);
    }

    /**
     * @return bool|string
     */
    private function locale()
    {
        $locale = false;
        if (get_locale() !== '') {
            $locale = substr(get_locale(), 0, 5);
        }

        return $locale;
    }

    /**
     * @param $output
     * @param $message
     */
    private function credentialInformations(&$output, $message)
    {
        $output .= sprintf(
            '<div><p>%s</p></div>',
            esc_html__(
                'Below you can see if your account is successfully hooked up to use PayPal PLUS.',
                'woo-paypalplus'
            ) . "<br />{$message}"
        );
    }

    /**
     * @param $output
     */
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

    /**
     * @param $output
     */
    private function sandboxMessage(&$output)
    {
        $msgSandbox = $this->isSandboxed()
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
