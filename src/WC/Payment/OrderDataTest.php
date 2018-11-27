<?php

namespace WCPayPalPlus\WC\Payment;

use Inpsyde\Lib\PayPal\Api\ItemList;
use Inpsyde\Lib\PayPal\Api\Item;

/**
 * Test data for passing to iframe to ensure PayPal Plus is enabled.
 *
 * @author Brandon Olivares
 */
class OrderDataTest extends OrderDataCommon
{
    public function get_total()
    {
        return 12.0;
    }

    public function get_subtotal()
    {
        return 10.0;
    }

    public function get_item_list()
    {
        $item_list = new ItemList();
        $item = new Item();
        $item->setName('Test Item')
            ->setCurrency(get_woocommerce_currency())
            ->setQuantity(1)
            ->setPrice(10.0);

        $item_list->addItem($item);
        return $item_list;
    }

    public function get_total_shipping()
    {
        return 2.0;
    }

    public function get_shipping_tax()
    {
        return 0.0;
    }

    public function should_include_tax_in_total()
    {
        return false;
    }

    public function get_subtotal_including_tax()
    {
        return 10.0;
    }

    public function get_items()
    {
        //
    }

    public function get_total_tax()
    {
        return 0.0;
    }

    public function get_total_discount()
    {
        return 0.0;
    }
}
