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

use WC_Order;

/**
 * Class OrderData
 *
 * @package WCPayPalPlus\WC\Payment
 */
class OrderData extends OrderDataCommon
{
    use OrderDataProcessor;

    /**
     * WooCommerce Order object.
     *
     * @var \WC_Order
     */
    private $order;

    /**
     * OrderData constructor.
     *
     * @param WC_Order $order WooCommerce order object.
     */
    public function __construct(WC_Order $order)
    {
        $this->order = $order;
    }

    /**
     * Returns an array of item data providers.
     *
     * @return OrderItemDataProvider[]
     */
    public function get_items()
    {
        $cart = $this->order->get_items();
        $items = [];

        foreach ($cart as $item) {
            $items[] = new OrderItemData($item);
        }

        foreach ($this->order->get_fees() as $fee) {
            $items[] = new OrderFeeData([
                'name' => $fee['name'],
                'qty' => 1,
                'line_subtotal' => $fee['line_total'],
            ]);
        }

        $discount = $this->get_total_discount();
        if ($discount > 0) {
            $items[] = new OrderDiscountData([
                'name' => 'Total Discount',
                'qty' => 1,
                'line_subtotal' => -$this->format($discount),
            ]);
        }

        return $items;
    }

    /**
     * Returns the total discount on the order.
     *
     * @return float
     */
    public function get_total_discount()
    {
        return $this->order->get_total_discount();
    }

    /**
     * Returns the total tax amount of the order.
     *
     * @return float
     */
    public function get_total_tax()
    {
        $tax = $this->order->get_total_tax();

        return $this->format($this->round($tax));
    }

    /**
     * Returns the total shipping cost of the order.
     *
     * @return float
     */
    public function get_total_shipping()
    {
        $shipping = $this->order->get_shipping_total();

        if ($this->get_shipping_tax() && preg_match('/\.\d{3,}/', $shipping)) {
            $shipping_tax = $this->order->get_shipping_tax();
            $shipping_total = $this->round($shipping + $shipping_tax);
            $shipping = $shipping_total - $this->round($shipping_tax);
        }

        return $shipping;
    }

    /**
     * Get total shipping tax.
     *
     * @return string
     */
    public function get_shipping_tax()
    {
        return $this->format($this->round($this->order->get_shipping_tax()));
    }

    /**
     * Get the subtotal including any additional taxes.
     *
     * This is used when the prices are given already including tax.
     *
     * @return string
     */
    public function get_subtotal_including_tax()
    {
        $shipping_diff = $this->round(
            $this->order->get_shipping_total() + $this->order->get_shipping_tax()
        );
        return $this->format($this->order->get_total() - $shipping_diff);
    }
}
