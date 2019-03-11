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

use WCPayPalPlus\Ipn\PaymentValidator;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Setting\Storable;
use WooCommerce;
use Exception;

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
    private $request;

    /**
     * @var Storable
     */
    private $settingRepository;

    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * OrderUpdaterFactory constructor.
     * @param WooCommerce $wooCommerce
     * @param OrderStatuses $orderStatuses
     * @param OrderFactory $orderFactory
     * @param Request $request
     * @param Storable $settingRepository
     */
    public function __construct(
        WooCommerce $wooCommerce,
        OrderStatuses $orderStatuses,
        OrderFactory $orderFactory,
        Request $request,
        Storable $settingRepository
    ) {

        $this->wooCommerce = $wooCommerce;
        $this->orderStatuses = $orderStatuses;
        $this->orderFactory = $orderFactory;
        $this->request = $request;
        $this->settingRepository = $settingRepository;
    }

    /**
     * @return OrderUpdater
     * @throws Exception
     */
    public function createByRequest()
    {
        $order = $this->orderFactory->createByRequest($this->request);
        $paymentValidator = new PaymentValidator($this->request, $order);

        return new OrderUpdater(
            $this->wooCommerce,
            $order,
            $this->settingRepository,
            $this->request,
            $paymentValidator,
            $this->orderStatuses
        );
    }
}
