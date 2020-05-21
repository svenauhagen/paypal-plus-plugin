<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Tests\Integration\Http;

use function Brain\Monkey\Actions\expectAdded as expectActionAdded;
use function Brain\Monkey\Functions\expect;
use WCPayPalPlus\Http\PayPalAssetsCache\CronScheduler;
use WCPayPalPlus\Http\PayPalAssetsCache\RemoteResourcesStorer;
use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary;
use WCPayPalPlus\Http\PayPalAssetsCache\AssetsStoreUpdater;
use WCPayPalPlus\Http\ServiceProvider as Testee;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class ServiceProviderTest
 * @package WCPayPalPlus\Tests\Unit\Http
 */
class ServiceProviderTest extends TestCase
{
    /**
     * Test All Services are Registered
     */
    public function testRegister()
    {
        /*
         * Stubs
         */
        $container = new Container();

        $container->addValue(
            'wp_filesystem',
            $this->getMockBuilder('\\WP_Filesystem_Base')->getMock()
        );
        $container->addValue(
            'cache_PayPal_Js_Files',
            true
        );

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'register',
            ['fileSystem']
        );

        /*
         * Expect to retrieve the base store dir from WordPress by calling `wp_upload_dir`
         */
        expect('wp_upload_dir')
            ->once()
            ->andReturn(
                [
                    'basedir' => uniqid(),
                ]
            );

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $container);

        /*
         * Assert the service registered
         */
        self::assertInstanceOf(
            CronScheduler::class,
            $container->get(CronScheduler::class)
        );
        self::assertInstanceOf(
            RemoteResourcesStorer::class,
            $container->get(RemoteResourcesStorer::class)
        );
        self::assertInstanceOf(
            ResourceDictionary::class,
            $container->get(ResourceDictionary::class)
        );
        self::assertInstanceOf(
            AssetsStoreUpdater::class,
            $container->get(AssetsStoreUpdater::class)
        );
    }
}
