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

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Order\OrderUpdaterFactory;
use Exception;
use WCPayPalPlus\Request\Request;
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
     * @var Logger
     */
    private $logger;

    /**
     * Ipn constructor.
     * @param Request $request
     * @param IpnVerifier $ipnVerifier
     * @param OrderUpdaterFactory $orderUpdaterFactory
     * @param OrderFactory $orderFactory
     * @param Logger $logger
     */
    public function __construct(
        Request $request,
        IpnVerifier $ipnVerifier,
        OrderUpdaterFactory $orderUpdaterFactory,
        OrderFactory $orderFactory,
        Logger $logger
    ) {

        $this->request = $request;
        $this->ipnVerifier = $ipnVerifier;
        $this->orderUpdaterFactory = $orderUpdaterFactory;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function checkResponse()
    {
        if (!$this->ipnVerifier->isVerified()) {
            $this->logger->error('Invalid IPN call', $this->request->all());
            status_header(500);
            return;
        }

        try {
            // Ensure an order exists
            $this->orderFactory->createByRequest($this->request);
            $this->updatePaymentStatus();
        } catch (Exception $exc) {
            $this->logger->error($exc);
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

        $this->logger->info(
            "Processing IPN. payment status: {$payment_status}",
            $this->request->all()
        );

        // Call Updater
        $updater->{$method}();
    }
}
