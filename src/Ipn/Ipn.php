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
use Exception;
use WCPayPalPlus\Request\Request;
use WC_Log_Levels as LogLevels;

/**
 * Handles responses from PayPal IPN.
 */
class Ipn
{
    const IPN_ENDPOINT_SUFFIX = '_ipn';

    /**
     * IPN Data Provider
     *
     * @var Request
     */
    private $request;

    /**
     * IPN Validator class
     *
     * @var IpnVerifier
     */
    private $ipnVerifier;

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
     * @param IpnVerifier $ipnVerifier
     * @param OrderUpdaterFactory $orderUpdaterFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Request $request,
        IpnVerifier $ipnVerifier,
        OrderUpdaterFactory $orderUpdaterFactory,
        OrderFactory $orderFactory
    ) {

        $this->request = $request;
        $this->ipnVerifier = $ipnVerifier;
        $this->orderUpdaterFactory = $orderUpdaterFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function checkResponse()
    {
        try {
            // Ensure an order exists
            $this->orderFactory->createByRequest($this->request);
        } catch (Exception $exc) {
            do_action(ACTION_LOG, LogLevels::ERROR, $exc->getMessage(), compact($exc));

            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw $exc;
            }

            return;
        }

        if ($this->ipnVerifier->isVerified()) {
            // TODO IPN Doesn't need to get any response from us?
            $this->updatePaymentStatus();
            exit;
        }

        do_action(ACTION_LOG, LogLevels::ERROR, 'Invalid IPN call', $this->request->all());
        // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
        wp_die('PayPal IPN Request Failure', 'PayPal IPN', ['response' => 500]);
    }

    /**
     * Update Payment Status
     *
     * @return void
     */
    private function updatePaymentStatus()
    {
        $payment_status = $this->request->get(Request::KEY_PAYMENT_STATUS);
        $updater = $this->orderUpdaterFactory->create();
        $method = "payment_status_{$payment_status}";

        if (!method_exists($updater, $method)) {
            do_action(
                ACTION_LOG,
                LogLevels::WARNING,
                "Processing IPN. payment status: {$payment_status}. Update method {$method} does not exists.",
                $this->request->all()
            );
        }

        do_action(
            ACTION_LOG,
            LogLevels::INFO,
            "Processing IPN. payment status: {$payment_status}",
            $this->request->all()
        );

        // Call Updater
        $updater->{$method}();
    }
}
