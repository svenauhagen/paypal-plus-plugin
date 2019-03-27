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

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class CartData
 *
 * @package WCPayPalPlus\Payment
 */
final class CartData extends OrderDataCommon
{
    use PriceFormatterTrait;

    /**
     * WooCommerce Cart.
     *
     * @var \WC_Cart
     */
    private $cart;

    /**
     * CartData constructor.
     *
     * @param \WC_Cart $cart WooCommerce cart.
     */
    public function __construct(\WC_Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Return the total taxes amount of the cart.
     *
     * @return float
     */
    public function totalTaxes()
    {
        // Include shipping and fee taxes
        $tax = $this->cart->get_taxes_total(true, false);
        $tax = $this->format($this->round($tax));

        return $tax;
    }

    /**
     * Returns the total shipping cost.
     *
     * @return float
     */
    public function shippingTotal()
    {
        $shippingTotal = $this->cart->get_shipping_total();
        $shippingTax = $this->cart->get_shipping_tax();

        // If shipping tax exists, and shipping has more than 2 decimals
        // Then calculate rounded shipping amount to prevent rounding errors
        if ($shippingTax && preg_match('/\.\d{3,}/', $shippingTotal)) {
            $shippingTotal = $this->round($shippingTotal + $shippingTax);
            $shippingTotal = $shippingTotal - $this->round($shippingTax);
        }

        return $this->format($this->round($shippingTotal));
    }

    /**
     * Returns the total discount in the cart.
     *
     * @return float
     */
    public function totalDiscount()
    {
        return $this->cart->get_discount_total();
    }

    /**
     * @inheritdoc
     */
    protected function items()
    {
        $items = [];
        $discount = $this->totalDiscount();

        foreach ($this->cart->get_cart() as $item) {
            $items[] = new CartItemData($item);
        }

        foreach ($this->cart->get_fees() as $fee) {
            $items[] = new FeeData($fee);
        }

        if ($discount > 0) {
            foreach ($this->cart->get_coupons('cart') as $code => $coupon) {
                $couponAmount = $this->cart->get_coupon_discount_amount($code);
                $items[] = new OrderDiscountData([
                    'name' => 'Cart Discount',
                    'qty' => '1',
                    'line_subtotal' => '-' . $this->format($couponAmount),
                ]);
            }
        }

        return $items;
    }
}
