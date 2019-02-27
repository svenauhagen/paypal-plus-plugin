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

use WCPayPalPlus\OrderFactory;

/**
 * Class OrderUpdaterFactory
 * @package WCPayPalPlus\Ipn
 */
class OrderUpdaterFactory
{
    /**
     * @param Data $ipnData
     * @param Request $ipnRequest
     * @return OrderUpdater
     * @throws \DomainException
     */
    public static function create(Data $ipnData, Request $ipnRequest)
    {
        $order = OrderFactory::createByIpnRequest($ipnRequest);
        $paymentValidator = new PaymentValidator($ipnData, $order);
        return new OrderUpdater(
            $order,
            $ipnData,
            $ipnRequest,
            $paymentValidator
        );
    }
}
