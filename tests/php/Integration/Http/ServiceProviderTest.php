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
     * Test Instance
     */
    public function testInstance()
    {
        $testee = new Testee();

        self::assertInstanceOf(Testee::class, $testee);
    }

    /* ------------------------------------------------------------------
       Test register
       --------------------------------------------------------------- */

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

    /* ------------------------------------------------------------------
       Test bootstrap
       --------------------------------------------------------------- */

    /**
     * Test bootstrap
     */
    public function testBootstrap()
    {
        /*
         * Stubs
         */
        $schedules = [];
        $filterCallbackHolder = null;

        $container = $this
            ->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $storeCron = $this->createMock(AssetsStoreUpdater::class);
        $cronScheduler = $this
            ->getMockBuilder(CronScheduler::class)
            ->disableOriginalConstructor()
            ->setMethods(['addWeeklyRecurrence', 'schedule'])
            ->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'bootstrap',
            []
        );

        /*
         * Expect to retrieve the CronScheduler from Container
         */
        $container
            ->expects($this->atLeastOnce())
            ->method('get')
            ->withConsecutive(
                [CronScheduler::class],
                [AssetsStoreUpdater::class]
            )
            ->willReturnOnConsecutiveCalls(
                $cronScheduler,
                $storeCron
            );

        /*
         * Expect to set the schedule
         */
        $cronScheduler
            ->expects($this->once())
            ->method('schedule');

        /*
         * Intercept the filter of `cron_schedules` for CronScheduler::addWeeklyRecurrence expectation
         */
        expect('add_filter')
            ->once()
            ->andReturnUsing(
                function ($string, $callback) use (&$filterCallbackHolder) {
                    $filterCallbackHolder = $callback;
                }
            );

        /*
         * Expects actions
         */
        expectActionAdded(CronScheduler::CRON_HOOK_NAME)
            ->once()
            ->with([$storeCron, 'execute']);

        /*
         * Execute Testee
         */
        $testeeMethod->invoke($testee, $container);

        apply_filters(
            'cron_schedules',
            function () use ($cronScheduler, $schedules, $filterCallbackHolder) {
                /*
                 * Expect to add weekly cron schedules
                 */
                $cronScheduler
                    ->expects($this->once())
                    ->method('addWeeklyRecurrence')
                    ->with($schedules);

                $filterCallbackHolder($schedules);
            }
        );
    }
}
