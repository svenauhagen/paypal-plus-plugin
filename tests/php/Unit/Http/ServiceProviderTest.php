<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Tests\Unit\Http;

use Monkey\Hook\HookStorage;
use function Brain\Monkey\Actions\expectAdded as expectActionAdded;
use function Brain\Monkey\Filters\expectAdded as expectFilterAdded;
use function Brain\Monkey\Functions\expect;
use WCPayPalPlus\Http\PayPalAssetsCache\AssetsStoreUpdater;
use WCPayPalPlus\Http\PayPalAssetsCache\CronScheduler;
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

    /**
     * Test bootstrap
     */
    public function testBootstrap()
    {
        /*
         * Stubs
         */
        expect('get_option')
            ->andReturn(['cache_paypal_js_files'=>true]);
        expect('wc_string_to_bool')
            ->andReturn(true);
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
         * Intercept the filter of `cron_schedules` for CronScheduler::addWeeklyRecurrence expectation
         */
        expect('add_filter')
            ->once()
            ->andReturnUsing(
                static function ($filter, ...$args) use (&$filterCallbackHolder) {
                    $container = \Brain\Monkey\Container::instance();
                    $container->hookStorage()->pushToAdded(
                        \Brain\Monkey\Hook\HookStorage::FILTERS,
                        $filter,
                        $args
                    );
                    $container->hookExpectationExecutor()->executeAddFilter($filter, $args);

                    $filterCallbackHolder = $args[0];

                    return true;
                }
            );
        expectFilterAdded('cron_schedules')->once();

        /*
         * Expects actions
         */
        expectActionAdded(CronScheduler::CRON_HOOK_NAME)
            ->once()
            ->with([$storeCron, 'update']);

        expectActionAdded('wp_enqueue_scripts')
            ->once()
            ->with([$cronScheduler, 'schedule'], 0);

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
