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

use WC_Order;

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class OrderData
 *
 * @package WCPayPalPlus\Payment
 */
final class OrderData extends OrderDataCommon
{
    use PriceFormatterTrait;

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
     * Returns the total discount on the order.
     *
     * @return float
     */
    public function totalDiscount()
    {
        return $this->order->get_total_discount();
    }

    /**
     * Returns the total tax amount of the order.
     *
     * @return float
     */
    public function totalTaxes()
    {
        $tax = $this->order->get_total_tax();

        return $this->format($this->round($tax));
    }

    /**
     * Returns the total shipping cost of the order.
     *
     * @return float
     */
    public function shippingTotal()
    {
        $shippingTotal = $this->order->get_shipping_total();
        $shippingTax = $this->order->get_shipping_tax();

        // If shipping tax exists, and shipping has more than 2 decimals
        // Then calculate rounded shipping amount to prevent rounding errors
        if ($shippingTax && preg_match('/\.\d{3,}/', $shippingTotal)) {
            $shippingTotal = $this->round($shippingTotal + $shippingTax);
            $shippingTotal = $shippingTotal - $this->round($shippingTax);
        }

        return $this->format($this->round($shippingTotal));
    }

    /**
     * @inheritdoc
     */
    protected function items()
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

        $discount = $this->totalDiscount();
        if ($discount > 0) {
            $items[] = new OrderDiscountData([
                'name' => 'Total Discount',
                'qty' => 1,
                'line_subtotal' => -$this->format($discount),
            ]);
        }

        return $items;
    }
}
