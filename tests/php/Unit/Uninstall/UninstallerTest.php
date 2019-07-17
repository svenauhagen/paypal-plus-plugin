<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Tests\Unit\Uninstall;

use function Brain\Monkey\Functions\expect;
use WCPayPalPlus\Tests\TestCase;
use WCPayPalPlus\Uninstall\Uninstaller as Testee;

/**
 * Class UninstallerTest
 * @package WCPayPalPlus\Tests\Unit\Uninstall
 */
class UninstallerTest extends TestCase
{
    /**
     * Test Instance
     */
    public function testInstance()
    {
        /*
         * Set Dependencies
         */
        $wpdb = $this->getMockBuilder('\\wpdb')->getMock();
        $fileSystem = $this->getMockBuilder('\\WP_Filesystem_Base')->getMock();

        /*
         * Execute test
         */
        $testee = new Testee($wpdb, $fileSystem);

        self::assertInstanceOf(Testee::class, $testee);
    }

    /* ----------------------------------------------------------------
       Test deleteCacheAssetsFiles
       ------------------------------------------------------------- */

    /**
     * Test deleteCacheAssetsFiles
     */
    public function testDeleteCacheAssetsFiles()
    {
        /*
         * Set Stubs
         */
        $baseDir = uniqid();

        /*
         * Set Dependencies
         */
        $wpdb = $this->getMockBuilder('\\wpdb')->getMock();
        $fileSystem = $this
            ->getMockBuilder('\\WP_Filesystem_Base')
            ->setMethods(['delete', 'exists'])
            ->getMock();

        /*
         * Set Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$wpdb, $fileSystem],
            'deleteCacheAssetsFiles',
            []
        );

        /*
         * Expect to retrieve the upload directory path
         */
        expect('wp_upload_dir')
            ->once()
            ->andReturn(
                [
                    'basedir' => $baseDir,
                ]
            );

        /*
         * Expect to clean the upload Dir
         */
        expect('untrailingslashit')
            ->once()
            ->with($baseDir)
            ->andReturn($baseDir);

        /*
         * Expect the file we want to delete exists
         */
        $fileSystem
            ->expects($this->once())
            ->method('exists')
            ->with("{$baseDir}/woo-paypalplus")
            ->willReturn(true);

        /*
         * Expect WP_Filesystem_Base::delete is called once to delete the file.
         */
        $fileSystem
            ->expects($this->once())
            ->method('delete')
            ->with("{$baseDir}/woo-paypalplus");

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee);
    }

    /**
     * Test that any file is deleted if the file list is empty
     */
    public function testDeleteCacheAssetsFilesWillNotDeleteAnythingIfFileListIsEmpty()
    {
        /*
        * Set Stubs
        */
        $baseDir = uniqid();

        /*
         * Set Dependencies
         */
        $wpdb = $this->getMockBuilder('\\wpdb')->getMock();
        $fileSystem = $this
            ->getMockBuilder('\\WP_Filesystem_Base')
            ->setMethods(['delete', 'exists'])
            ->getMock();

        /*
         * Set Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$wpdb, $fileSystem],
            'deleteCacheAssetsFiles',
            []
        );

        /*
         * Expect to retrieve the upload directory path
         */
        expect('wp_upload_dir')
            ->once()
            ->andReturn(
                [
                    'basedir' => $baseDir,
                ]
            );

        /*
         * Expect to clean the upload Dir
         */
        expect('untrailingslashit')
            ->once()
            ->with($baseDir)
            ->andReturn($baseDir);

        /*
         * Expect the file we want to delete does not exists
         */
        $fileSystem
            ->expects($this->once())
            ->method('exists')
            ->with("{$baseDir}/woo-paypalplus")
            ->willReturn(false);

        /*
         * Expect WP_Filesystem_Base::delete is called once to delete the file.
         */
        $fileSystem
            ->expects($this->never())
            ->method('delete');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee);
    }
}
