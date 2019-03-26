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
     * Calculate the order total.
     *
     * @return float
     */
    public function get_total()
    {
        $total = $this->get_subtotal()
            + $this->get_total_shipping()
            + $this->get_shipping_tax()
            + $this->get_total_tax();

        $total = $this->format($total);

        return $total;
    }

    /**
     * Calculate the order subtotal.
     *
     * @return float
     */
    public function get_subtotal()
    {
        $subtotal = 0;
        $items = $this->get_item_list()->getItems();

        foreach ($items as $item) {
            $product_price = $item->getPrice();
            $item_price = (float)$product_price * $item->getQuantity();
            $subtotal += $item_price;
        }

        return $subtotal;
    }

    /**
     * Generated a new ItemList object from the items of the current order
     *
     * @return ItemList
     */
    public function get_item_list()
    {
        $item_list = new ItemList();
        foreach ($this->get_items() as $order_item) {
            $item_list->addItem($this->get_item($order_item));
        }

        return $item_list;
    }

    /**
     * Creates a single Order Item for the Paypal API
     *
     * @param OrderItemDataProvider $data Order|Cart item.
     *
     * @return Item
     */
    public function get_item(OrderItemDataProvider $data)
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

        if (!empty($sku)) {
            $item->setSku($sku);// Similar to `item_number` in Classic API.
        }

        return $item;
    }
}
