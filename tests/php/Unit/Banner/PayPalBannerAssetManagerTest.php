<?php

namespace WCPayPalPlus\Tests\Unit\Banner;

use PHPUnit\Framework\MockObject\MockObject;
use WCPayPalPlus\Assets\PayPalBannerAssetManager;
use WCPayPalPlus\Assets\PayPalBannerAssetManager as Testee;
use WCPayPalPlus\Tests\TestCase;

class PayPalBannerAssetManagerTest extends TestCase
{
    public function testEnqueueFrontEndScripts()
    {
        /*
         * SUT
         */
        $testee = $this->createTestee(
            [
                'isEnqueueAllowed',
                'enqueueScripts',
            ]
        );

        $testee
            ->expects(parent::once())
            ->method('isEnqueueAllowed')
            ->will(parent::returnValue(true));

        $testee->expects(parent::once())->method('enqueueScripts');

        $testee->enqueueFrontEndScripts();
    }

    public function testNotEnqueueFrontEndScripts()
    {
        /*
         * SUT
         */
        $testee = $this->createTestee(
            [
                'isEnqueueAllowed',
                'enqueueScripts',
            ]
        );

        $testee
            ->expects(parent::once())
            ->method('isEnqueueAllowed')
            ->will(parent::returnValue(false));

        $testee->expects(parent::never())->method('enqueueScripts');

        $testee->enqueueFrontEndScripts();
    }


    /**
     * @param array $methods
     *
     * @return MockObject&PayPalBannerAssetManager
     */
    protected function createTestee(array $methods)
    {
        return $this->buildTesteeMock(
            Testee::class,
            [],
            $methods,
            ''
        )->getMock();
    }
}
