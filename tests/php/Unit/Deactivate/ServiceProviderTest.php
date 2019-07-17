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
use WCPayPalPlus\Deactivate\Deactivator;
use WCPayPalPlus\Deactivate\ServiceProvider as Testee;
use WCPayPalPlus\PluginProperties;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class ServiceProviderTest
 * @package WCPayPalPlus\Tests\Unit\Deactivate
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
         * Setup Stubs
         */
        $pluginPropertiesFilePath = uniqid();

        $container = $this
            ->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $deactivator = $this
            ->getMockBuilder(Deactivator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pluginProperties = $this
            ->getMockBuilder(PluginProperties::class)
            ->disableOriginalConstructor()
            ->setMethods(['filePath'])
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
         * Expect to retrieve the Deactivator from the container
         */
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [PluginProperties::class],
                [Deactivator::class]
            )
            ->willReturnOnConsecutiveCalls(
                $pluginProperties,
                $deactivator
            );

        /*
         * Retrieve the main plugin file path required by `register_deactivation_hook`
         */
        $pluginProperties
            ->expects($this->once())
            ->method('filePath')
            ->willReturn($pluginPropertiesFilePath);

        /*
         * Expect to call the register_deactivation_hook with the right parameters
         */
        expect('register_deactivation_hook')
            ->once()
            ->with($pluginPropertiesFilePath, [$deactivator, 'deactivate']);

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $container);
    }
}
