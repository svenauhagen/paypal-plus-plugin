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
         * Execute Test
         */
        $testee = new Testee();

        self::assertInstanceOf(Testee::class, $testee);
    }

    /* -------------------------------------------------------------
       schedule
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
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
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
         * Execute Test
         */
        $testeeMethod->invoke($testee);
    }

    /* -------------------------------------------------------------
       addWeeklyRecurrence
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
