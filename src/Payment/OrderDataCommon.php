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

use Inpsyde\Lib\PayPal\Api\Item;
use Inpsyde\Lib\PayPal\Api\ItemList;

use WCPayPalPlus\Utils\PriceFormatterTrait;

/**
 * Class OrderDataCommon
 *
 * @package WCPayPalPlus\Payment
 */
abstract class OrderDataCommon implements OrderDataProvider
{
    use PriceFormatterTrait;

    /**
     * Calculate the order total
     *
     * @return float|int|string
     * @throws \InvalidArgumentException
     */
    public function total()
    {
        $total = $this->subTotal()
            + $this->shippingTotal()
            + $this->totalTaxes();

        $total = $this->format($this->round($total));

        return $total;
    }

    /**
     * Calculate the order subtotal
     *
     * @return float|int
     * @throws \InvalidArgumentException
     */
    public function subTotal()
    {
        $subtotal = 0;
        $items = $this->itemsList()->getItems();

        foreach ($items as $item) {
            $product_price = $item->getPrice();
            $item_price = (float)$product_price * $item->getQuantity();
            $subtotal += $item_price;
        }

        return $subtotal;
    }

    /**
     * Retrieve the Items
     *
     * @return ItemList
     * @throws \InvalidArgumentException
     */
    public function itemsList()
    {
        $item_list = new ItemList();
        foreach ($this->items() as $order_item) {
            $item_list->addItem($this->item($order_item));
        }

        return $item_list;
    }

    /**
     * Creates a single Order Item for the Paypal API
     *
     * @param OrderItemDataProvider $data
     * @return Item
     * @throws \InvalidArgumentException
     */
    protected function item(OrderItemDataProvider $data)
    {
        $name = html_entity_decode($data->get_name(), ENT_NOQUOTES, 'UTF-8');
        $currency = get_woocommerce_currency();
        $sku = $data->get_sku();
        $price = $data->get_price();

        $item = new Item();
        $item
            ->setName($name)
            ->setCurrency($currency)
            ->setQuantity($data->get_quantity())
            ->setPrice($price);

        /*
         * Address Rounding problems
         *
         * The main problem here is how WooCommerce tackle the taxes applied to prices and how
         * PayPal needs to have those prices.
         *
         * Because of intrinsic rounds problems WooCommerce deal with taxes as cent values, means
         * the numbers used to calculate taxes contains a lot of information.
         *
         * PayPal want to have prices 2 decimal rounded, if not the Item::setPrice will format in that way.
         * What we did in previous versions was to get the price of the product (total) divided by
         * the quantity see $data->get_price(), this could produce rounding problems because
         * the division result could contains more than 2 decimal points, then we pass that value
         * to Item::setPrice that will round it, practically adding some more cents to the price.
         *
         * So the price calculated by WooCommerce will not fit the price calculated by our implementation.
         * The good solution would work as WooCommerce (using cent values) but actually the logic
         * is too complicated that need a complete separated implementation.
         *
         * So as workaround to send to paypal all the items instead only one as done in previous
         * versions we implemented the following solution.
         */
        $paypalTotal = $item->getPrice() * $data->get_quantity();
        if ($paypalTotal !== $price) {
            $item
                ->setName($name . 'x' . $data->get_quantity())
                ->setCurrency($currency)
                ->setQuantity(1)
                ->setPrice($price);
        }

        if (!empty($sku)) {
            $item->setSku($sku);// Similar to `item_number` in Classic API.
        }

        return $item;
    }

    /**
     * Returns an array of item data providers.
     *
     * @return OrderItemDataProvider[]
     */
    abstract protected function items();
}
