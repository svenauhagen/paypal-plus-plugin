<?php

namespace WCPayPalPlus\Tests\Functional\Order;

use WC_Order;
use WCPayPalPlus\Order\OrderFactory;
use WCPayPalPlus\Payment\PatchProvider;
use WCPayPalPlus\Tests\TestCase;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

class OrderFactoryTest extends TestCase
{
    public function testCreateByOrderKey()
    {
        /*
         * Stubs
         */
        $orderId = mt_rand(1, 100);
        $orderKey = uniqid();
        $orderKeyAsJson = json_encode(
            [
                PatchProvider::WOOCOMMERCE_ORDER_KEY_NAME => $orderKey,
                PatchProvider::WOOCOMMERCE_ORDER_ID_NAME => $orderId,
            ]
        );
        $wooCommerceOrder = $this->createMock(WC_Order::class);

        /*
         * SUT
         */
        $orderFactory = new OrderFactory();

        /*
         * Stubbing
         */
        when('wc_get_order_id_by_order_key')->justReturn($orderId);

        /*
         * Expectations
         */
        expect('wc_get_order')->with($orderId)->andReturn($wooCommerceOrder);

        /*
         * Execute Test
         */
        $result = $orderFactory->createByOrderKey($orderKey);
        self::assertEquals($wooCommerceOrder, $result);

        $result = $orderFactory->createByOrderKey($orderKeyAsJson);
        self::assertEquals($wooCommerceOrder, $result);
    }
}
