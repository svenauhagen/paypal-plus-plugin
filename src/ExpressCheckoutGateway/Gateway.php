<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\ExpressCheckoutGateway;

use Inpsyde\Lib\PayPal\Api\Payment;
use Inpsyde\Lib\PayPal\Exception\PayPalConnectionException;
use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ApiContextFactory;
use WCPayPalPlus\Api\CredentialValidator;
use WCPayPalPlus\Gateway\MethodsTrait;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PaymentPatcher;
use WCPayPalPlus\Payment\PaymentPatchFactory;
use WCPayPalPlus\Setting\ExpressCheckoutRepositoryTrait;
use WCPayPalPlus\Setting\ExpressCheckoutStorable;
use WCPayPalPlus\Setting\GatewaySharedSettingsTrait;
use WCPayPalPlus\Payment\PaymentExecutionFactory;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Payment\Session;
use WCPayPalPlus\Refund\RefundFactory;
use WCPayPalPlus\Setting\SettingsGatewayModel;
use WCPayPalPlus\Setting\SharedRepositoryTrait;
use WCPayPalPlus\WC\CheckoutDropper;
use WooCommerce;
use WC_Payment_Gateway;
use RuntimeException;
use WC_Order;

/**
 * Class Gateway
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
final class Gateway extends WC_Payment_Gateway implements ExpressCheckoutStorable
{
    use SharedRepositoryTrait;
    use ExpressCheckoutRepositoryTrait;
    use GatewaySharedSettingsTrait;
    use MethodsTrait;

    const GATEWAY_ID = 'paypal_express';
    const GATEWAY_TITLE_METHOD = 'PayPal Express Checkout';
    const ACTION_AFTER_PAYMENT_EXECUTION = 'woopaypalplus.after_express_checkout_payment_execution';
    const ACTION_AFTER_PAYMENT_PATCH = 'woopaypalplus.after_express_checkout_payment_patch';

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
    private $session;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * @var CheckoutDropper
     */
    private $checkoutDropper;

    /**
     * @var PaymentPatchFactory
     */
    private $paymentPatchFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Gateway constructor.
     * @param CredentialValidator $credentialValidator
     * @param SettingsGatewayModel $settingsModel
     * @param RefundFactory $refundFactory
     * @param OrderFactory $orderFactory
     * @param PaymentExecutionFactory $paymentExecutionFactory
     * @param Session $session
     * @param CheckoutDropper $checkoutDropper
     * @param PaymentPatchFactory $paymentPatchFactory
     * @param Logger $logger
     */
    public function __construct(
        CredentialValidator $credentialValidator,
        SettingsGatewayModel $settingsModel,
        RefundFactory $refundFactory,
        OrderFactory $orderFactory,
        PaymentExecutionFactory $paymentExecutionFactory,
        Session $session,
        CheckoutDropper $checkoutDropper,
        PaymentPatchFactory $paymentPatchFactory,
        Logger $logger
    ) {

        $this->credentialValidator = $credentialValidator;
        $this->settingsModel = $settingsModel;
        $this->refundFactory = $refundFactory;
        $this->orderFactory = $orderFactory;
        $this->paymentExecutionFactory = $paymentExecutionFactory;
        $this->session = $session;
        $this->checkoutDropper = $checkoutDropper;
        $this->paymentPatchFactory = $paymentPatchFactory;
        $this->logger = $logger;
        $this->id = self::GATEWAY_ID;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->method_title = self::GATEWAY_TITLE_METHOD;
        $this->description = $this->get_option('description');
        $this->method_description = esc_html_x(
            'Allow customers to Checkout Product and cart directly.',
            'gateway-settings',
            'woo-paypalplus'
        );

        $this->has_fields = true;
        $this->supports = [
            'products',
            'refunds',
        ];
    }

    /**
     * TODO May be logic of Patching and Payment Execution can be split. Use actions?
     *      About this see the similar code for Plus Gateway.
     *
     * @param int $orderId
     * @return array
     */
    public function process_payment($orderId)
    {
        assert(is_int($orderId));

        $order = null;
        $paymentId = $this->session->get(Session::PAYMENT_ID);
        $payerId = $this->session->get(Session::PAYER_ID);

        if (!$payerId || !$paymentId || !$orderId) {
            $this->logger->error('Payment Excecution: Insufficient data to make payment.');
            // TODO Where redirect the user.
            return [
                'result' => 'failed',
                'redirect' => '',
            ];
        }

        try {
            $order = $this->orderFactory->createById($orderId);
        } catch (RuntimeException $exc) {
            $this->logger->error('Payment Execution: ' . $exc);
            $this->checkoutDropper->abortCheckout();
        }

        $paymentPatcher = $this->paymentPatchFactory->create(
            $order,
            $paymentId,
            $this->invoicePrefix(),
            ApiContextFactory::getFromConfiguration()
        );

        $isSuccessPatched = $paymentPatcher->execute();

        /**
         * Action After Payment Patch
         *
         * @param PaymentPatcher $paymentPatcher
         * @oparam bool $isSuccessPatched
         * @param CheckoutDropper $checkoutDropper
         */
        do_action(
            self::ACTION_AFTER_PAYMENT_PATCH,
            $paymentPatcher,
            $isSuccessPatched,
            $this->checkoutDropper
        );

        if (!$isSuccessPatched) {
            $this->checkoutDropper->abortCheckout();

            return [
                'result' => 'failed',
                'redirect' => $order->get_cancel_order_url(),
            ];
        }

        $payment = $this->paymentExecutionFactory->create(
            $order,
            $payerId,
            $paymentId,
            ApiContextFactory::getFromConfiguration()
        );

        try {
            $payment = $payment->execute();

            /**
             * Action After Payment has been Executed
             *
             * @param Payment $payment
             * @param WC_Order $order
             */
            do_action(self::ACTION_AFTER_PAYMENT_EXECUTION, $payment, $order);
        } catch (PayPalConnectionException $exc) {
            $this->logger->error('Payment Execution: ' . $exc);
            $this->checkoutDropper->abortCheckout();
        }

        return [
            'result' => 'success',
            'redirect' => $order->get_checkout_order_received_url(),
        ];
    }
}
