<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\PlusGateway;

use const WCPayPalPlus\ACTION_LOG;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Api\CredentialProvider;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Notice;
use WCPayPalPlus\Setting\PlusRepositoryHelper;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\WC\Payment\PaymentExecutionFactory;
use WCPayPalPlus\WC\Payment\PaymentCreatorFactory;
use WCPayPalPlus\WC\Payment\Session;
use WCPayPalPlus\WC\Refund\RefundFactory;
use WCPayPalPlus\WC\WCWebExperienceProfile;
use WC_Order_Refund;
use WooCommerce;

/**
 * Class Gateway
 * @package WCPayPalPlus\WC
 */
class Gateway extends \WC_Payment_Gateway implements PlusStorable
{
    use PlusRepositoryHelper;

    const GATEWAY_ID = 'paypal_plus';
    const GATEWAY_TITLE_METHOD = 'PayPal PLUS';

    /**
     * @var FrameRenderer
     */
    private $frameView;

    /**
     * @var CredentialProvider
     */
    private $credentialProvider;

    /**
     * @var CredentialValidator
     */
    private $credentialValidator;

    /**
     * @var GatewaySettingsModel
     */
    private $settingsModel;

    /**
     * @var RefundFactory
     */
    private $refundFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var PaymentExecutionFactory
     */
    private $paymentExecutionFactory;

    /**
     * @var PaymentCreatorFactory
     */
    private $paymentCreatorFactory;

    /**
     * @var Session
     */
    private $paymentSession;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * Gateway constructor.
     * @param WooCommerce $wooCommerce
     * @param FrameRenderer $frameView
     * @param CredentialProvider $credentialProvider
     * @param CredentialValidator $credentialValidator
     * @param GatewaySettingsModel $settingsModel
     * @param RefundFactory $refundFactory
     * @param OrderFactory $orderFactory
     * @param PaymentExecutionFactory $paymentExecutionFactory
     * @param PaymentCreatorFactory $paymentCreatorFactory
     * @param Session $paymentSession
     */
    public function __construct(
        WooCommerce $wooCommerce,
        FrameRenderer $frameView,
        CredentialProvider $credentialProvider,
        CredentialValidator $credentialValidator,
        GatewaySettingsModel $settingsModel,
        RefundFactory $refundFactory,
        OrderFactory $orderFactory,
        PaymentExecutionFactory $paymentExecutionFactory,
        PaymentCreatorFactory $paymentCreatorFactory,
        Session $paymentSession
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->frameView = $frameView;
        $this->credentialProvider = $credentialProvider;
        $this->credentialValidator = $credentialValidator;
        $this->settingsModel = $settingsModel;
        $this->refundFactory = $refundFactory;
        $this->orderFactory = $orderFactory;
        $this->paymentExecutionFactory = $paymentExecutionFactory;
        $this->paymentCreatorFactory = $paymentCreatorFactory;
        $this->paymentSession = $paymentSession;

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
        $this->form_fields = $this->settingsModel->settings();
    }

    /**
     * @param int $orderId
     * @param null $amount
     * @param string $reason
     * @return bool
     */
    public function process_refund($orderId, $amount = null, $reason = '')
    {
        $order = $this->orderFactory->createById($orderId);

        if (!$order instanceof WC_Order_Refund) {
            return false;
        }

        if (!$this->can_refund_order($order)) {
            return false;
        }

        $apiContext = ApiContextFactory::getFromConfiguration();
        $refund = $this->refundFactory->create($order, $amount, $reason, $apiContext);

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
        $credentials = $this->credentialProvider->byRequest($this->isSandboxed());
        $apiContext = ApiContextFactory::getFromCredentials($credentials);
        list($maybeValid, $message) = $this->credentialValidator->ensureCredential($apiContext);

        switch ($maybeValid) {
            case true:
                $config = [
                    'checkout_logo' => $this->get_option('checkout_logo'),
                    'local_id' => $this->experienceProfileId(),
                    'brand_name' => $this->get_option('brand_name'),
                    'country' => $this->get_option('country'),
                ];
                $webProfile = new WCWebExperienceProfile(
                    $config,
                    $apiContext
                );
                $optionKey = $this->experienceProfileKey();
                $_POST[$this->get_field_key($optionKey)] = $webProfile->save_profile();
                break;
            case false:
                // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
                unset($_POST[$this->get_field_key('enabled')]);
                $this->enabled = 'no';

                $this->add_error(sprintf(
                    __(
                        'Your API credentials are either missing or invalid: %s',
                        'woo-paypalplus'
                    ),
                    $message
                ));
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

        list($isValid) = $this->credentialValidator->ensureCredential(
            ApiContextFactory::getFromConfiguration()
        );

        $isValid and $this->sandboxMessage($output);
        !$isValid and $this->invalidPaymentMessage($output);

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
     * @param string $key
     * @param string|array|object $data
     * @return false|string
     */
    /** @noinspection PhpUnusedParameterInspection */
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
            <td class="forminp <?= sanitize_html_class($data['class']) ?>">
                <?= $data['html'] ?>
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
        $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
        $payerId = filter_input(INPUT_GET, 'PayerID', FILTER_SANITIZE_STRING);
        $paymentId = filter_input(INPUT_GET, 'paymentId', FILTER_SANITIZE_STRING);
        $orderId = $this->paymentSession->get(Session::ORDER_ID);

        if (!$paymentId) {
            $paymentId = $this->paymentSession->get(Session::PAYMENT_ID);
        }
        if (!$token || !$payerId || !$paymentId || !$orderId) {
            return;
        }

        $this->paymentSession->set(Session::TOKEN, $token);
        $this->paymentSession->set(Session::PAYER_ID, $payerId);

        $order = $this->orderFactory->createById($orderId);

        try {
            $payment = $this->paymentExecutionFactory->create(
                $order,
                $payerId,
                $paymentId,
                ApiContextFactory::getFromConfiguration()
            );
            $payment->execute();
        } catch (PayPalConnectionException $exc) {
            do_action(
                ACTION_LOG,
                \WC_Log_Levels::ERROR,
                'payment_execution_exception: ' . $exc->getMessage(),
                compact($exc)
            );

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
     * @return void
     */
    private function form()
    {
        $paymentUrl = $this->createPayment();

        $data = [
            'useraction' => 'commit',
            'showLoadingIndicator' => true,
            'approvalUrl' => $paymentUrl,
            'placeholder' => 'ppplus',
            'mode' => $this->isSandboxed() ? 'sandbox' : 'live',
            'country' => $this->wooCommerce->customer->get_billing_country(),
            'language' => $this->locale(),
            'buttonLocation' => 'outside',
            'showPuiOnSandbox' => true,
        ];

        $this->frameView->render($data);
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

        if (strpos($checkoutLogoUrl, 'https') === false) {
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
     * @return string
     */
    private function createPayment()
    {
        $url = (string)$this->paymentSession->get(Session::APPROVAL_URL);

        if (!$url) {
            try {
                $paymentCreator = $this->paymentCreatorFactory->create(
                    $this,
                    $this,
                    $this->paymentSession
                );
                $paymentCreator = $paymentCreator->create();
            } catch (\Exception $exc) {
                do_action(
                    ACTION_LOG,
                    \WC_Log_Levels::ERROR,
                    'create_payment_exception: ' . $exc->getMessage(),
                    compact($exc)
                );

                return $url;
            }

            $this->paymentSession->set(Session::PAYMENT_ID, $paymentCreator->getId());

            $url = htmlspecialchars_decode($paymentCreator->getApprovalLink());
            $this->paymentSession->set(Session::APPROVAL_URL, $url);
        }

        return $url;
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

    /**
     * @return string
     */
    private function experienceProfileKey()
    {
        return $this->isSandboxed()
            ? PlusStorable::OPTION_PROFILE_ID_SANDBOX_NAME
            : PlusStorable::OPTION_PROFILE_ID_LIVE_NAME;
    }
}
