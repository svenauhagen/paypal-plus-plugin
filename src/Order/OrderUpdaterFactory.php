<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Order;

use WCPayPalPlus\Ipn\Data;
use WCPayPalPlus\Ipn\PaymentValidator;
use WCPayPalPlus\Ipn\Request;

/**
 * Class OrderUpdaterFactory
 * @package WCPayPalPlus\Ipn
 */
class OrderUpdaterFactory
{
    /**
     * @var OrderStatuses
     */
    private $orderStatuses;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var Request
     */
    private $ipnRequest;

    /**
     * @var Data
     */
    private $ipnData;

    /**
     * OrderUpdaterFactory constructor.
     * @param OrderStatuses $orderStatuses
     * @param OrderFactory $orderFactory
     * @param Request $ipnRequest
     * @param Data $ipnData
     */
    public function __construct(
        OrderStatuses $orderStatuses,
        OrderFactory $orderFactory,
        Request $ipnRequest,
        Data $ipnData
    ) {

        $this->orderStatuses = $orderStatuses;
        $this->orderFactory = $orderFactory;
        $this->ipnRequest = $ipnRequest;
        $this->ipnData = $ipnData;
    }

    /**
     * @return OrderUpdater
     * @throws \DomainException
     */
    public function create()
    {
        $order = $this->orderFactory->createByIpnRequest($this->ipnRequest);
        $paymentValidator = new PaymentValidator($this->ipnData, $order);

        return new OrderUpdater(
            $order,
            $this->ipnData,
            $this->ipnRequest,
            $paymentValidator,
            $this->orderStatuses
        );
    }
}
