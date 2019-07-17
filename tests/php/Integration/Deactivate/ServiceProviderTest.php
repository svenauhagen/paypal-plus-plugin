<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Tests\Integration\Deactivate;

use WCPayPalPlus\Deactivate\Deactivator;
use WCPayPalPlus\Deactivate\ServiceProvider as Testee;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class ServiceProviderTest
 * @package WCPayPalPlus\Tests\Unit\Deactivate
 */
class ServiceProviderTest extends TestCase
{
    /**
     * Test register
     */
    public function testRegister()
    {
        /*
         * Setup Stubs
         */
        $container = new Container();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'register',
            []
        );

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $container);

        self::assertInstanceOf(
            Deactivator::class,
            $container->get(Deactivator::class)
        );
    }
}
