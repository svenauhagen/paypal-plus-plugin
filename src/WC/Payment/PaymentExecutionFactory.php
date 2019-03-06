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
 * Class PaymentExecutionFactory
 * @package WCPayPalPlus\WC\Payment
 */
class PaymentExecutionFactory
{
    /**
     * @param WC_Order $order
     * @param string $payerId
     * @param string $paymentId
     * @param ApiContext $apiContext
     * @return WCPaymentExecution
     */
    public function create(WC_Order $order, $payerId, $paymentId, $apiContext)
    {
        assert(is_string($payerId));
        assert(is_string($paymentId));

        $data = new PaymentExecutionData(
            $order,
            $payerId,
            $paymentId,
            $apiContext
        );

        $success = new PaymentExecutionSuccess($data);

        // TODO May be we want to rename the class by removing the WC Prefix
        return new WCPaymentExecution($data, $success);
    }
}
