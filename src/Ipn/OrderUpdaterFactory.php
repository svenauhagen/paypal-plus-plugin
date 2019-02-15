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

/**
 * Class OrderUpdaterFactory
 * @package WCPayPalPlus\Ipn
 */
class OrderUpdaterFactory
{
    public static function create(Data $data)
    {
        $order = $data->woocommerceOrder();
        return new OrderUpdater(
            $order,
            $data,
            new PaymentValidator($data, $order)
        );
    }
}
