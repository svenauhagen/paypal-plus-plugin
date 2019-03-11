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

use const WCPayPalPlus\ACTION_LOG;
use WC_Log_Levels as LogLevels;
use WCPayPalPlus\Ipn\Ipn;
use WCPayPalPlus\Payment\PaymentCreatorFactory;
use WCPayPalPlus\Payment\Session;
use WCPayPalPlus\Setting\PlusStorable;
use WCPayPalPlus\Utils\AjaxJsonRequest;
use Exception;
use OutOfBoundsException;
use WooCommerce;

// TODO Problem about the class it's use `AjaxJsonRequest` making impossible to call in a context
//      that is not ajax. When an error occur, throw an exception and let the client do whatever it want.

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

    /**
     * @var PaymentCreatorFactory
     */
    private $paymentCreatorFactory;

    /**
     * @var PlusStorable
     */
    private $settingRepository;

    /**
     * @var AjaxJsonRequest
     */
    private $ajaxJsonRequest;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * CartCheckout constructor.
     * @param PlusStorable $settingRepository
     * @param PaymentCreatorFactory $paymentCreatorFactory
     * @param AjaxJsonRequest $ajaxJsonRequest
     * @param WooCommerce $wooCommerce
     * @param Session $session
     */
    public function __construct(
        PlusStorable $settingRepository,
        PaymentCreatorFactory $paymentCreatorFactory,
        AjaxJsonRequest $ajaxJsonRequest,
        WooCommerce $wooCommerce,
        Session $session
    ) {

        // TODO Must use ExpressCheckoutStorable
        $this->settingRepository = $settingRepository;
        $this->paymentCreatorFactory = $paymentCreatorFactory;
        $this->ajaxJsonRequest = $ajaxJsonRequest;
        $this->wooCommerce = $wooCommerce;
        $this->session = $session;
    }

    /**
     * @return void
     */
    public function createOrder()
    {
        $orderId = '';
        $returnUrl = wc_get_checkout_url();
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
        } catch (Exception $exc) {
            $this->ajaxJsonRequest->sendJsonError([
                'message' => $exc->getMessage(),
            ]);

            do_action(
                ACTION_LOG,
                LogLevels::ERROR,
                'create_payment_exception: ' . $exc->getMessage(),
                compact($exc)
            );

            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new $exc;
            }
        }

        $this->ajaxJsonRequest->sendJsonSuccess([
            'orderID' => $orderId,
        ]);
    }

    /**
     * Store the data needed for payment into session
     * @throws OutOfBoundsException
     */
    public function storePaymentData()
    {
        $dataToSanitize = [
            self::INPUT_PAYER_ID_NAME => FILTER_SANITIZE_STRING,
            self::INPUT_PAYMENT_ID_NAME => FILTER_SANITIZE_STRING,
        ];
        $data = filter_input_array(INPUT_POST, $dataToSanitize);
        $data = array_filter($data);

        if (count($data) !== count($dataToSanitize)) {
            $this->ajaxJsonRequest->sendJsonError(['success' => false]);
        }

        $this->session->set(Session::PAYER_ID, $data[self::INPUT_PAYER_ID_NAME]);
        $this->session->set(Session::PAYMENT_ID, $data[self::INPUT_PAYMENT_ID_NAME]);
        $this->session->set(Session::CHOSEN_PAYMENT_METHOD, Gateway::GATEWAY_ID);

        $this->ajaxJsonRequest->sendJsonSuccess(['success' => true]);
    }
}
