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

use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Ipn constructor.
     * @param Request $request
     * @param IpnVerifier $ipnVerifier
     * @param OrderUpdaterFactory $orderUpdaterFactory
     * @param OrderFactory $orderFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Request $request,
        IpnVerifier $ipnVerifier,
        OrderUpdaterFactory $orderUpdaterFactory,
        OrderFactory $orderFactory,
        LoggerInterface $logger
    ) {

        $this->request = $request;
        $this->ipnVerifier = $ipnVerifier;
        $this->orderUpdaterFactory = $orderUpdaterFactory;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
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
            $this->logger->error('Invalid IPN call', $this->request->all());
            // TODO Doesn't make any sense here to have the `wp_die` we don't show anything, an exception will be more helpful.
            //      May be we can set just the header response to 500 just to give back something.
            //      Check also other code where we use the `wp_die`
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
