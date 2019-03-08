<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC\Refund;

use Inpsyde\Lib\PayPal\Rest\ApiContext;
use WCPayPalPlus\Order\OrderStatuses;
use WC_Order_Refund;

/**
 * Class RefundFactory
 * @package WCPayPalPlus\WC\Refund
 */
class RefundFactory
{
    /**
     * @var OrderStatuses
     */
    private $orderStatuses;

    /**
     * RefundFactory constructor.
     * @param OrderStatuses $orderStatuses
     */
    public function __construct(OrderStatuses $orderStatuses)
    {
        $this->orderStatuses = $orderStatuses;
    }

    /**
     * Create a new Refund Order
     *
     * @param WC_Order_Refund $order
     * @param float $amount
     * @param string $reason
     * @param ApiContext $apiContext
     * @return WCRefund
     */
    public function create($order, $amount, $reason, ApiContext $apiContext)
    {
        assert(is_float($amount));
        assert(is_string($reason));

        $refundData = new RefundData(
            $order,
            $amount,
            $reason,
            $apiContext
        );

        return new WCRefund($refundData, $apiContext, $this->orderStatuses);
    }
}
