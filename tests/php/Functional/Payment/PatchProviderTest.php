<?php

namespace WCPayPalPlus\Tests\Functional\Payment;

use Inpsyde\Lib\PayPal\Api\Patch;
use PHPUnit\Framework\MockObject\MockObject;
use WC_Order;
use WCPayPalPlus\Payment\OrderDataProvider;
use WCPayPalPlus\Payment\PatchProvider;
use WCPayPalPlus\Tests\TestCase;
use function Brain\Monkey\Filters\expectApplied;
use function Brain\Monkey\Functions\when;

class PatchProviderTest extends TestCase
{
    /**
     * @var MockObject|WC_Order
     */
    private $wooCommerceOrder;

    /**
     * @var MockObject|OrderDataProvider
     */
    private $orderDataProvider;

    public function testCustom()
    {
        /*
         * Stubs
         */
        $orderId = uniqid();
        $orderKey = uniqid();

        /*
         * SUT
         */
        $patchProvider = new PatchProvider(
            $this->wooCommerceOrder,
            $this->orderDataProvider
        );

        /*
         * Stubbing
         */
        $this->wooCommerceOrder->method('get_id')->willReturn($orderId);
        $this->wooCommerceOrder->method('get_order_key')->willReturn($orderKey);

        /*
         * Expectations
         */
        expectApplied(PatchProvider::FILTER_USE_LEGACY_CUSTOM_PATCH_DATA)
            ->once()
            ->andReturn(false);

        /*
         * Execute Test
         */
        $result = $patchProvider->custom();

        self::assertInstanceOf(Patch::class, $result);
        self::assertEquals(PatchProvider::CUSTOM_OPERATION, $result->getOp());
        self::assertEquals(PatchProvider::CUSTOM_PATH, $result->getPath());
        self::assertEquals($orderKey, $result->getValue());
    }

    public function testCustomContainsLegacyValue()
    {
        /*
         * Stubs
         */
        $orderId = uniqid();
        $orderKey = uniqid();

        /*
         * SUT
         */
        $patchProvider = new PatchProvider(
            $this->wooCommerceOrder,
            $this->orderDataProvider
        );

        /*
         * Stubbing
         */
        $this->wooCommerceOrder->method('get_id')->willReturn($orderId);
        $this->wooCommerceOrder->method('get_order_key')->willReturn($orderKey);

        when('wp_json_encode')->returnArg(1);

        /*
         * Expectations
         */
        expectApplied(PatchProvider::FILTER_USE_LEGACY_CUSTOM_PATCH_DATA)
            ->once()
            ->andReturn(true);

        /*
         * Execute Test
         */
        $result = $patchProvider->custom();

        self::assertInstanceOf(Patch::class, $result);
        self::assertEquals(PatchProvider::CUSTOM_OPERATION, $result->getOp());
        self::assertEquals(PatchProvider::CUSTOM_PATH, $result->getPath());
        self::assertEquals(
            [
                PatchProvider::WOOCOMMERCE_ORDER_ID_NAME => $orderId,
                PatchProvider::WOOCOMMERCE_ORDER_KEY_NAME => $orderKey,
            ],
            $result->getValue()
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->initializeDependencies();
    }

    private function initializeDependencies()
    {
        $this->wooCommerceOrder = $this->wooCommerceOrder();
        $this->orderDataProvider = $this->orderDataProvider();
    }

    private function wooCommerceOrder()
    {
        $mock = $this
            ->getMockBuilder(WC_Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['get_id', 'get_order_key'])
            ->getMock();

        return $mock;
    }

    private function orderDataProvider()
    {
        $mock = $this
            ->getMockBuilder(OrderDataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
