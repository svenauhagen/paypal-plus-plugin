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

use WCPayPalPlus\Http\PayPalAssetsCache\AssetsStoreUpdater;
use function Brain\Monkey\Functions\expect;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use WCPayPalPlus\Http\PayPalAssetsCache\CronScheduler as Testee;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class CronSchedulerTest
 * @package WCPayPalPlus\Tests\Unit\Http\PayPalAssetsCache
 */
class CronSchedulerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Test Instance
     */
    public function testInstance()
    {
        /*
         * Setup Dependencies
         */
        $assetsStoreUpdater = $this->createMock(AssetsStoreUpdater::class);

        /*
         * Execute Test
         */
        $testee = new Testee($assetsStoreUpdater);

        self::assertInstanceOf(Testee::class, $testee);
    }

    /* -------------------------------------------------------------
       Test schedule
       ---------------------------------------------------------- */

    /**
     * Test schedule
     */
    public function testSchedule()
    {
        /*
         * Stubs
         */
        $time = time();

        /*
         * Setup Dependencies
         */
        $assetsStoreUpdater = $this
            ->getMockBuilder(AssetsStoreUpdater::class)
            ->disableOriginalConstructor()
            ->setMethods(['update'])
            ->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$assetsStoreUpdater],
            'schedule',
            []
        );

        /*
         * Expect to call WordPress function `wp_next_scheduled` to know if
         * an event is already scheduled.
         *
         * In this case the even will not be schedule yet
         */
        expect('wp_next_scheduled')
            ->once()
            ->with(Testee::CRON_HOOK_NAME)
            ->andReturn(false);

        /*
         * Then expect to schedule the event
         */
        expect('time')
            ->once()
            ->andReturn($time);

        expect('wp_schedule_event')
            ->with($time + MINUTE_IN_SECONDS, 'weekly', Testee::CRON_HOOK_NAME);

        /*
         * Expect the scripts files are updated the first time when schedule a new event.
         */
        $assetsStoreUpdater
            ->expects($this->once())
            ->method('update');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee);
    }

    /* -------------------------------------------------------------
       Test addWeeklyRecurrence
       ---------------------------------------------------------- */

    /**
     * Test addWeeklyRecurrence
     */
    public function testAddWeeklyRecurrency()
    {
        /*
         * Stubs
         */
        $schedules = [];

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'addWeeklyRecurrence',
            []
        );

        /*
         * Expect translation for newly schedule recurrence
         */
        expect('__')
            ->once()
            ->with('Weekly', 'woo-paypalplus')
            ->andReturn('Weekly');

        /*
         * Execute Tests
         */
        $result = $testeeMethod->invoke($testee, $schedules);

        self::assertEquals(
            [
                'weekly' => [
                    'interval' => WEEK_IN_SECONDS,
                    'display' => 'Weekly',
                ],
            ],
            $result
        );
    }

    /**
     * Test addWeeklyRecurrence doesn't override existing entry
     */
    public function testAddWeeklyRecurrenceDoesNotOverrideExistingEntry()
    {
        /*
         * Stubs
         */
        $schedules = [
            'weekly' => [
                'interval' => mt_rand(0, 1),
                'display' => uniqid(),
            ],
        ];

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'addWeeklyRecurrence',
            []
        );

        /*
         * Execute Tests
         */
        $result = $testeeMethod->invoke($testee, $schedules);

        self::assertEquals($schedules, $result);
    }
}
