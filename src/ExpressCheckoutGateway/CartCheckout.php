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

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use PayPal\Exception\PayPalConnectionException;
use WCPayPalPlus\Ipn\Ipn;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Setting\ExpressCheckoutStorable;
use WCPayPalPlus\Setting\Storable;
use WCPayPalPlus\Utils\AjaxJsonRequest;
use Exception;
use WooCommerce;

/**
 * Class CartCheckout
 * @package WCPayPalPlus\ExpressCheckoutGateway
 */
class CartCheckout
{
    const INPUT_PAYER_ID_NAME = 'payerID';
    const INPUT_PAYMENT_ID_NAME = 'paymentID';

    const TASK_CREATE_ORDER = 'createOrder';
    const TASK_STORE_PAYMENT_DATA = 'storePaymentData';

    const ACTION_STORE_PAYMENT_DATA = 'woopaypalplus.exc_store_payment_data';

    /**
     * @var PaymentCreatorFactory
     */
    private $paymentCreatorFactory;

    /**
     * @var Storable
     */
    private $settingRepository;

    /**
     * @var AjaxJsonRequest
     */
    private $ajaxJsonRequest;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Request
     */
    private $request;

    /**
     * CartCheckout constructor.
     * @param ExpressCheckoutStorable $settingRepository
     * @param PaymentCreatorFactory $paymentCreatorFactory
     * @param AjaxJsonRequest $ajaxJsonRequest
     * @param WooCommerce $wooCommerce
     * @param Logger $logger
     * @param Request $request
     */
    public function __construct(
        ExpressCheckoutStorable $settingRepository,
        PaymentCreatorFactory $paymentCreatorFactory,
        AjaxJsonRequest $ajaxJsonRequest,
        WooCommerce $wooCommerce,
        Logger $logger,
        Request $request
    ) {

        $this->settingRepository = $settingRepository;
        $this->paymentCreatorFactory = $paymentCreatorFactory;
        $this->ajaxJsonRequest = $ajaxJsonRequest;
        $this->wooCommerce = $wooCommerce;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * @return void
     */
    public function createOrder()
    {
        if ($this->wooCommerce->cart->is_empty()) {
            $this->ajaxJsonRequest->sendJsonError([
                'message' => esc_html_x(
                    'Cannot create an order with an empty cart.',
                    'express-checkout',
                    'woo-paypalplus'
                ),
            ]);
        }

        $orderId = '';
        $returnUrl = $this->settingRepository->returnUrl();
        $notifyUrl = $this->wooCommerce->api_request_url(
            Gateway::GATEWAY_ID . Ipn::IPN_ENDPOINT_SUFFIX
        );
        $paymentCreator = $this->paymentCreatorFactory->create(
            $this->settingRepository,
            $returnUrl,
            $notifyUrl
        );

        // TODO Prevent to execute more than once?
        try {
            $payment = $paymentCreator->create();
            $orderId = $payment->getId();
        } catch (PayPalConnectionException $exc) {
            wc_add_notice($exc->getMessage(), 'error');
            $this->logger->error($exc->getData(), [$orderId]);
            $this->ajaxJsonRequest->sendJsonError([
                'message' => $exc->getMessage(),
            ]);
        } catch (Exception $exc) {
            wc_add_notice($exc->getMessage(), 'error');
            $this->logger->error($exc, [$orderId]);
            $this->ajaxJsonRequest->sendJsonError([
                'message' => $exc->getMessage(),
            ]);
        }

        $this->ajaxJsonRequest->sendJsonSuccess([
            'orderID' => $orderId,
        ]);
    }

    /**
     * Store the data needed for payment into session
     */
    public function storePaymentData()
    {
        $payerId = $this->request->get(self::INPUT_PAYER_ID_NAME, FILTER_SANITIZE_STRING);
        $paymentId = $this->request->get(self::INPUT_PAYMENT_ID_NAME, FILTER_SANITIZE_STRING);

        if (!$payerId || !$paymentId) {
            wc_add_notice(
                esc_html__('Invalid Payment or Payer ID.', 'woo-paypalplus'),
                'error'
            );
            $this->logger->error('Invalid Payment or Payer ID.');
            $this->ajaxJsonRequest->sendJsonError(['success' => false]);
        }

        /**
         * Store Payment Data
         *
         * @param string $payerId
         * @param string $paymentId
         */
        do_action(self::ACTION_STORE_PAYMENT_DATA, $payerId, $paymentId);

        $this->ajaxJsonRequest->sendJsonSuccess(['success' => true]);
    }
}
