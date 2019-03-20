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

use UnexpectedValueException;
use DomainException;
use WC_Order;
use WC_Order_Refund;
use Exception;
use WCPayPalPlus\Request\Request;
use RuntimeException;

/**
 * Class OrderFactory
 * @package WCPayPalPlus
 */
class OrderFactory
{
    /**
     * @param $orderKey
     * @return WC_Order|WC_Order_Refund
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    public function createByOrderKey($orderKey)
    {
        assert(is_string($orderKey));

        $orderKey = $this->orderKeyByJson($orderKey);
        // Cast to int because the function return a int as string.
        $orderId = (int)wc_get_order_id_by_order_key($orderKey);
        $order = $this->createById($orderId);

        $this->bailIfInvalidOrder($order);

        return $order;
    }

    /**
     * Create and order by the given Id
     *
     * @param int $orderId
     * @return WC_Order|WC_Order_Refund
     * @throws RuntimeException
     */
    public function createById($orderId)
    {
        assert(is_int($orderId));

        if (!$orderId) {
            throw new RuntimeException("Cannot create order by value {$orderId}");
        }

        $order = wc_get_order($orderId);

        if (!in_array(get_class($order), [WC_Order::class, WC_Order_Refund::class], true)) {
            throw new RuntimeException("Cannot create order by value {$orderId}");
        }

        return $order;
    }

    /**
     * Old plugin version used a json string to extract the order id and or the order key,
     * sounds like this approach caused issues so we switched to use just the order_key that doesn't
     * never change.
     *
     * To prevent orders are not full filled after the update of the plugin, we'll keep it for a while.
     *
     * @deprecated since 2.0.0 This will be removed in a future version
     * @param $customData
     * @return string
     */
    private function orderKeyByJson($customData)
    {
        assert(is_string($customData));

        $orderKey = '';

        // We are sure we used json object.
        if (strpos($customData, '{') === false) {
            return $customData;
        }

        $custom = json_decode($customData);
        $jsonErrorNone = json_last_error() === JSON_ERROR_NONE;

        if ($custom && $jsonErrorNone && is_object($custom)) {
            $orderKey = isset($custom->order_key) ? $custom->order_key : '';
        }

        return $orderKey ?: $customData;
    }

    /**
     * @param $order
     * @throws UnexpectedValueException
     */
    private function bailIfInvalidOrder($order)
    {
        if (!$order instanceof WC_Order) {
            throw new UnexpectedValueException('No way to retrieve the order by IPN custom data.');
        }
    }
}
