<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Http\PayPalAssetsCache;

use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary as Testee;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class ResourceDictionaryTest
 * @package WCPayPalPlus\Http\PayPalAssetsCache
 */
class ResourceDictionaryTest extends TestCase
{
    /**
     * Test Instance
     */
    public function testInstance()
    {
        /*
         * Stubs
         */
        $list = [
            uniqid() => uniqid(),
        ];

        /*
         * Test Instance
         */
        $testee = new Testee($list);

        self::assertInstanceOf(Testee::class, $testee);
    }

    /**
     * Test resourceList
     */
    public function testResourceList()
    {
        /*
         * Stubs
         */
        $list = [
            uniqid() => uniqid(),
        ];

        /*
         * Test Instance
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$list],
            'resourcesList',
            []
        );

        /*
         * Execute Test
         */
        $result = $testeeMethod->invoke($testee);

        self::assertEquals($list, $result);
    }
}
