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

/**
 * Class OrderStatusesComparer
 * @package WCPayPalPlus\WC
 */
class OrderStatuses
{
    const ORDER_STATUS_PREFIX = 'wc-';
    const ORDER_STATUS_PENDING = 'pending';
    const ORDER_STATUS_PROCESSING = 'processing';
    const ORDER_STATUS_ON_HOLD = 'on-hold';
    const ORDER_STATUS_COMPLETED = 'completed';
    const ORDER_STATUS_CANCELLED = 'cancelled';
    const ORDER_STATUS_REFUNDED = 'refunded';
    const ORDER_STATUS_FAILED = 'failed';
    const ORDER_STATUSES = [
        self::ORDER_STATUS_PENDING,
        self::ORDER_STATUS_PROCESSING,
        self::ORDER_STATUS_ON_HOLD,
        self::ORDER_STATUS_COMPLETED,
        self::ORDER_STATUS_CANCELLED,
        self::ORDER_STATUS_REFUNDED,
        self::ORDER_STATUS_FAILED,
    ];

    /**
     * Check if two given orders are the same
     *
     * @param $status
     * @param $statusToCompareAgainst
     * @return bool
     */
    public function orderStatusIs($status, $statusToCompareAgainst)
    {
        assert(is_string($status));
        assert(is_string($statusToCompareAgainst));

        $statusNormalized = $this->normalizeStatus($status);

        return $statusNormalized === $statusToCompareAgainst;
    }

    /**
     * Normalize an order status string by get rid of the WooCommerce prefix
     *
     * @param $status
     * @return bool|string
     */
    private function normalizeStatus($status)
    {
        assert(is_string($status));

        return strpos($status, self::ORDER_STATUS_PREFIX)
            ? substr($status, strlen(self::ORDER_STATUS_PREFIX))
            : $status;
    }
}
