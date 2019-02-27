<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus;

use WCPayPalPlus\Ipn;

/**
 * Class OrderFactory
 * @package WCPayPalPlus
 */
class OrderFactory
{
    /**
     * @param Ipn\Request $ipnRequest
     * @return \WC_Order|null
     * @throws \Exception
     */
    public static function createByIpnRequest(Ipn\Request $ipnRequest)
    {
        list($orderId, $orderKey) = self::customOrderData($ipnRequest);

        $order = wc_get_order($orderId);
        $order or $order = self::createByOrderKey($orderKey);

        self::bailIfInvalidOrder($order);

        return $order;
    }

    /**
     * @param $orderKey
     * @return bool|\WC_Order
     */
    public static function createByOrderKey($orderKey)
    {
        assert(is_string($orderKey));

        $orderId = wc_get_order_id_by_order_key($orderKey);
        $order = wc_get_order($orderId);

        self::bailIfInvalidOrder($order);

        return $order;
    }

    /**
     * @param Ipn\Request $ipnRequest
     * @return array
     * @throws \DomainException
     * @throws \UnexpectedValueException
     */
    private static function customOrderData(Ipn\Request $ipnRequest)
    {
        $data = $ipnRequest->get(Ipn\Request::KEY_CUSTOM);
        if (!$data) {
            throw new \DomainException('Invalid Custom Data');
        }

        $data = json_decode($data);
        if ($data === null) {
            throw new \UnexpectedValueException('Decoding IPN Custom Data, produced no value');
        }

        $orderId = isset($data->order_id) ? (int)$data->order_id : 0;
        $orderKey = isset($data->order_key) ? $data->order_key : '';

        if (!$orderId && !$orderKey) {
            throw new \UnexpectedValueException('Order ID nor Order Key are valid data.');
        }

        return [
            $orderId,
            $orderKey,
        ];
    }

    /**
     * @param $order
     * @throws \UnexpectedValueException
     */
    private static function bailIfInvalidOrder($order)
    {
        if (!$order instanceof \WC_Order) {
            throw new \UnexpectedValueException('No way to retrieve the order by IPN custom data.');
        }
    }
}
