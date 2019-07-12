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

use function Brain\Monkey\Functions\when;
use function Brain\Monkey\Functions\expect;
use UnexpectedValueException;
use WCPayPalPlus\Http\PayPalAssetsCache\CronScheduler;
use WCPayPalPlus\Http\PayPalAssetsCache\RemoteResourcesStorer;
use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary;
use WCPayPalPlus\Http\PayPalAssetsCache\StoreCron;
use WCPayPalPlus\Http\ServiceProvider as Testee;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Tests\TestCase;
use WP_Filesystem_Base;

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
         * Stubs WordPress functions
         */
        when('WP_Filesystem')->justReturn(true);

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
         * Expect to get a valid Wp Filesystem Instance
         */
        $testee
            ->expects($this->once())
            ->method('fileSystem')
            ->willReturn($this->getMockBuilder('\\WP_Filesystem_Base')->getMock());

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
            StoreCron::class,
            $container->get(StoreCron::class)
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
            ->expects($this->once())
            ->method('get')
            ->with(CronScheduler::class)
            ->willReturn($cronScheduler);

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

    /* ------------------------------------------------------------------
       Test fileSystem
       --------------------------------------------------------------- */

    /**
     * Test fileSystem
     */
    public function testFileSystem()
    {
        global $wp_filesystem;

        $fileSystem = $this->getMockBuilder('\\WP_Filesystem_Base')->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'fileSystem',
            []
        );

        /*
         * Expect to call WP_Filesystem in order to create the right instance of the
         * Wp Filesystem
         */
        expect('WP_Filesystem')
            ->once()
            ->andReturnUsing(
                function () use ($fileSystem) {
                    global $wp_filesystem;
                    $wp_filesystem = $fileSystem;
                    return true;
                }
            );

        /*
         * Execute Test
         */
        $result = $testeeMethod->invoke($testee);

        self::assertEquals($wp_filesystem, $result);
    }

    /**
     * Test fileSystem doesnt work because WP_Filesystem cannot create a valid filesystem instance
     */
    public function testFileSystemThrowUnexpectedExceptionBecauseOfWpFilesystemReturnFalse()
    {
        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'fileSystem',
            []
        );

        /*
         * Expect to call WP_Filesystem in order to create the right instance of the
         * Wp Filesystem
         */
        expect('WP_Filesystem')
            ->once()
            ->andReturnUsing(
                function () {
                    return false;
                }
            );

        /*
         * Expect Exception
         */
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('There were problem in initializing Wp FileSystem');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee);
    }
}
