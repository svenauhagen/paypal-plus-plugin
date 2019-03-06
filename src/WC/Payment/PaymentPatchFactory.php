<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC\Payment;

use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WC_Order;

/**
 * Class PaymentPatchFactory
 * @package WCPayPalPlus\WC\Payment
 */
class PaymentPatchFactory
{
    /**
     * @param WC_Order $order
     * @param string $paymentId
     * @param string $invoicePrefix
     * @param ApiContext $context
     * @return WCPaymentPatch
     */
    public function create(WC_Order $order, $paymentId, $invoicePrefix, ApiContext $context)
    {
        assert(is_string($paymentId));
        assert(is_string($invoicePrefix));

        $orderData = new OrderData($order);
        $patchProvider = new PatchProvider($order, $orderData);

        $patchData = new PaymentPatchData(
            $order,
            $paymentId,
            $invoicePrefix,
            $context,
            $patchProvider
        );

        // TODO May be we want to rename the class by removing the WC Prefix
        return new WCPaymentPatch($patchData);
    }
}
