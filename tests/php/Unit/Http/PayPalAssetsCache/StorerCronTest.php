<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Tests\Unit\Http\PayPalAssetsCache;

use WCPayPalPlus\Http\PayPalAssetsCache\RemoteResourcesStorer;
use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary;
use WCPayPalPlus\Http\PayPalAssetsCache\AssetsStoreUpdater as Testee;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class StorerClockTest
 * @package WCPayPalPlus\Tests\Unit\Http\PayPalAssetsCache
 */
class StorerCronTest extends TestCase
{
    /**
     * Test Instance
     */
    public function testInstance()
    {
        /*
         * Setup Dependencies
         */
        $remoteResourcesStorer = $this->createMock(RemoteResourcesStorer::class);
        $resourceDictionary = $this->createMock(ResourceDictionary::class);
        $baseStorePath = uniqid();

        /*
         * Execute Test
         */
        $testee = new Testee($remoteResourcesStorer, $resourceDictionary, $baseStorePath);

        self::assertInstanceOf(Testee::class, $testee);
    }

    /*
     * Test update
     */
    public function testUpdate()
    {
        /*
         * Set Dependencies
         */
        $remoteResourcesStorer = $this
            ->getMockBuilder(RemoteResourcesStorer::class)
            ->disableOriginalConstructor()
            ->setMethods(['update'])
            ->getMock();

        $resourceDictionary = $this
            ->getMockBuilder(ResourceDictionary::class)
            ->disableOriginalConstructor()
            ->setMethods(['resourcesList'])
            ->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$remoteResourcesStorer, $resourceDictionary],
            'update',
            []
        );

        /*
         * Expect RemoteResourceStorer::update get the right parameters
         */
        $remoteResourcesStorer
            ->expects($this->once())
            ->method('update')
            ->with($resourceDictionary);

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee);
    }
}
