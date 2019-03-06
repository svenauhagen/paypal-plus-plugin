<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Ipn;

use const WCPayPalPlus\ACTION_LOG;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Order\OrderUpdaterFactory;

/**
 * Handles responses from PayPal IPN.
 */
class Ipn
{
    const IPN_ENDPOINT_SUFFIX = '_ipn';

    /**
     * IPN Data Provider
     *
     * @var Data
     */
    private $request;

    /**
     * IPN Validator class
     *
     * @var Validator
     */
    private $ipnValidator;

    /**
     * @var OrderUpdaterFactory
     */
    private $orderUpdaterFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * Ipn constructor.
     * @param Request $request
     * @param Validator $validator
     * @param OrderUpdaterFactory $orderUpdaterFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Request $request,
        Validator $validator,
        OrderUpdaterFactory $orderUpdaterFactory,
        OrderFactory $orderFactory
    ) {

        $this->request = $request;
        $this->ipnValidator = $validator;
        $this->orderUpdaterFactory = $orderUpdaterFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function checkResponse()
    {
        try {
            // Ensure an order exists
            $this->orderFactory->createByIpnRequest($this->request);
        } catch (\Exception $exc) {
            do_action(ACTION_LOG, \WC_Log_Levels::ERROR, $exc->getMessage(), compact($exc));

            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw $exc;
            }

            return;
        }

        if ($this->ipnValidator->validate()) {
            $this->valid_response();
            exit;
        }

        do_action(ACTION_LOG, \WC_Log_Levels::ERROR, 'Invalid IPN call', $this->request->all());
        // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        wp_die('PayPal IPN Request Failure', 'PayPal IPN', ['response' => 500]);
    }

    /**
     * TODO Could be private
     *
     * There was a valid response.
     */
    public function valid_response()
    {
        $payment_status = $this->request->get(Request::KEY_PAYMENT_STATUS);
        $updater = $this->orderUpdaterFactory->create();

        if (method_exists($updater, 'payment_status_' . $payment_status)) {
            do_action(
                ACTION_LOG,
                \WC_Log_Levels::INFO,
                'Processing IPN. payment status: ' . $payment_status,
                $this->request->all()
            );
            $updater->{"payment_status_{$payment_status}"}();

            return true;
        }

        return false;
    }
}
