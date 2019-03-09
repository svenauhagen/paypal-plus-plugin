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
use LogicException;

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
     * TODO IPN Doesn't need to get any response from us?
     *      If so, ensure all OrderUpdater methods will return the same value types.
     *
     * @return void
     * @throws Exception
     */
    public function checkResponse()
    {
        if (!$this->ipnVerifier->isVerified()) {
            $this->log(LogLevels::ERROR, 'Invalid IPN call', $this->request->all());
            // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
            wp_die('PayPal IPN Request Failure', 'PayPal IPN', ['response' => 500]);
        }

        try {
            // Ensure an order exists
            $this->orderFactory->createByRequest($this->request);
            $this->updatePaymentStatus();
            // TODO Why exiting here?
            exit;
        } catch (Exception $exc) {
            // TODO W
            $this->logException($exc);
            return;
        }
    }

    /**
     * Update Payment Status
     *
     * @return void
     * @throws LogicException
     */
    private function updatePaymentStatus()
    {
        $payment_status = $this->request->get(Request::KEY_PAYMENT_STATUS);
        $method = "payment_status_{$payment_status}";
        $updater = $this->orderUpdaterFactory->create();

        if (!method_exists($updater, $method)) {
            throw new LogicException("Method OrderUpdater::{$method} does not exists.");
        }

        $this->log(
            LogLevels::INFO,
            "Processing IPN. payment status: {$payment_status}",
            $this->request->all()
        );

        // Call Updater
        $updater->{$method}();
    }

    /**
     * Log Exceptions and re-throw them if `WP_DEBUG` is set to true
     *
     * @param Exception $exception
     * @throws Exception
     */
    private function logException(Exception $exception)
    {
        $this->log(LogLevels::ERROR, $exception->getMessage(), compact($exception));

        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw $exception;
        }
    }

    /**
     * Log Action
     *
     * TODO Could be an utility function or a trait? I prefer traits as helpers.
     *      We could add two kind of loggers, normal and from exception, so we can re-throw it in one place.
     *      See self::logExceptionAction
     *
     * @param string $level
     * @param string $message
     * @param array $data
     * @return void
     */
    private function log($level, $message, array $data)
    {
        do_action(ACTION_LOG, $level, "IPN: {$message}", $data);
    }
}
