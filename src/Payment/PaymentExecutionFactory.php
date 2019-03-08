<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Payment;

use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WC_Order;
use WooCommerce;

/**
 * Class PaymentExecutionFactory
 * @package WCPayPalPlus\Payment
 */
class PaymentExecutionFactory
{
    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * PaymentExecutionFactory constructor.
     * @param WooCommerce $wooCommerce
     */
    public function __construct(WooCommerce $wooCommerce)
    {
        $this->wooCommerce = $wooCommerce;
    }

    /**
     * @param WC_Order $order
     * @param string $payerId
     * @param string $paymentId
     * @param ApiContext $apiContext
     * @return PaymentPerformer
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

        $success = new PaymentExecutionSuccess($this->wooCommerce, $data);

        return new PaymentPerformer($data, $success);
    }
}
