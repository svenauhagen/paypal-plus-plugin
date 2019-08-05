<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Tests\Unit\Deactivate;

use function Brain\Monkey\Functions\expect;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use WCPayPalPlus\Deactivate\Deactivator as Testee;
use WCPayPalPlus\Http\PayPalAssetsCache\CronScheduler;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class DeactivatorTest
 * @package WCPayPalPlus\Tests\Unit\Deactivate
 */
class DeactivatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Test Instance
     */
    public function testInstance()
    {
        $testee = new Testee();

        self::assertInstanceOf(Testee::class, $testee);
    }

    /**
     * Test deactivate
     *
     * Ensure all of the internal methods are called
     */
    public function testDeactivate()
    {
        /*
         * Setup testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'deactivate',
            ['unscheduleCron']
        );

        /*
         * Expect to unschedule the cron events
         */
        $testee
            ->expects($this->once())
            ->method('unscheduleCron');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee);
    }

    /**
     * Test unscheduleCron
     */
    public function testUnscheduleCron()
    {
        /*
         * Setup Stubs
         */
        $timestamp = mt_rand(0, 20);

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'unscheduleCron',
            []
        );

        /*
         * Expect to retrieve the next schedule event timestamp and
         * call `wp_unschedule_event` to unschedule the cron events
         */
        expect('wp_next_scheduled')
            ->once()
            ->with(CronScheduler::CRON_HOOK_NAME)
            ->andReturn($timestamp);

        expect('wp_unschedule_event')
            ->once()
            ->with($timestamp, CronScheduler::CRON_HOOK_NAME);

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee);
    }
}
