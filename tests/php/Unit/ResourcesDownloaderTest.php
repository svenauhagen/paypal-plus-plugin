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
use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary;
use WCPayPalPlus\Http\PayPalAssetsCache\RemoteResourcesStorer as Testee;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class ResourcesDownloaderTest
 * @package WCPayPalPlus\Tests\Unit\Http\PayPalAssetsCache
 */
class ResourcesDownloaderTest extends TestCase
{
    /**
     * Test Instance
     */
    public function testInstance()
    {
        /*
         * Setup Dependencies
         */
        $wpFileSystem = $this
            ->getMockBuilder('\\WP_Filesystem_Base')
            ->getMock();

        /*
         * Execute Test
         */
        $testee = new Testee($wpFileSystem);

        self::assertInstanceOf(Testee::class, $testee);
    }

    /* -------------------------------------------------------------------
       Test update
       ---------------------------------------------------------------- */

    /**
     * Test update
     */
    public function testUpdate()
    {
        /*
         * Stubs
         *
         * - The httpResponse variable simulate a response array returned by wp_safe_remote_post
         */
        $localFilePath = uniqid();
        $remoteResourcePath = uniqid();
        $fileContent = uniqid();
        $httpResponse = [
            'body' => $fileContent,
        ];

        $resourcesList = [
            $localFilePath => $remoteResourcePath,
        ];

        $baseLocalPath = uniqid();

        $resourceDictionary = $this
            ->getMockBuilder(ResourceDictionary::class)
            ->setMethods(['resourcesList'])
            ->getMock();

        /*
         * Setup Dependencies
         */
        $wpFileSystem = $this
            ->getMockBuilder('\\WP_Filesystem_Base')
            ->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$wpFileSystem],
            'update',
            ['storeFileContent']
        );

        /*
         * Expect to retrieve the resource information by the resource dictionary
         * one at time to be able to remotely get the file content.
         */
        $resourceDictionary
            ->expects($this->once())
            ->method('resourcesList')
            ->willReturn($resourcesList);

        /*
         * Expect to retrieve the file content by using WordPress function
         * wp_safe_remote_post.
         */
        expect('wp_safe_remote_post')
            ->once()
            ->with($remoteResourcePath)
            ->andReturn($httpResponse);

        /*
         * Then expect to extract the body from the http response
         */
        expect('wp_remote_retrieve_body')
            ->once()
            ->with($httpResponse)
            ->andReturn($fileContent);

        /*
         * Expect to clean the baseLocalPath before build the final file path
         */
        expect('trailingslashit')
            ->once()
            ->with($baseLocalPath)
            ->andReturn("{$baseLocalPath}/");

       /*
        * Expect to store the file content
        */
       $testee
           ->expects($this->once())
           ->method('storeFileContent')
           ->with("{$baseLocalPath}/{$localFilePath}", $fileContent);

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $resourceDictionary, $baseLocalPath);
    }

    /**
     * Test update not performed because empty dictionary
     */
    public function testUpdateNotPerformedBecauseEmptyDictionary()
    {
        /*
         * Stubs
         */
        $baseLocalPath = uniqid();

        $resourceDictionary = $this
            ->getMockBuilder(ResourceDictionary::class)
            ->setMethods(['resourcesList'])
            ->getMock();

        /*
         * Setup Dependencies
         */
        $wpFileSystem = $this
            ->getMockBuilder('\\WP_Filesystem_Base')
            ->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$wpFileSystem],
            'update',
            ['storeFileContent']
        );

        /*
         * Expect empty Dictionary list
         */
        $resourceDictionary
            ->expects($this->once())
            ->method('resourcesList')
            ->willReturn([]);

        /*
         * Expect `storeFileContent` is never called
         */
        $testee
            ->expects($this->never())
            ->method('storeFileContent');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $resourceDictionary, $baseLocalPath);
    }

    /**
     * Test store file isn't performed because empty file content
     */
    public function testUpdateNotPerformedBecauseEmptyFileContent()
    {
        /*
         * Stubs
         *
         * - The httpResponse variable simulate a response array returned by wp_safe_remote_post
         */
        $localFilePath = uniqid();
        $remoteResourcePath = uniqid();
        $fileContent = '';
        $httpResponse = [
            'body' => $fileContent,
        ];

        $resourcesList = [
            $localFilePath => $remoteResourcePath,
        ];

        $baseLocalPath = uniqid();

        $resourceDictionary = $this
            ->getMockBuilder(ResourceDictionary::class)
            ->setMethods(['resourcesList'])
            ->getMock();

        /*
         * Setup Dependencies
         */
        $wpFileSystem = $this
            ->getMockBuilder('\\WP_Filesystem_Base')
            ->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$wpFileSystem],
            'update',
            ['storeFileContent']
        );

        /*
         * Expect to retrieve the resource information by the resource dictionary
         * one at time to be able to remotely get the file content.
         */
        $resourceDictionary
            ->expects($this->once())
            ->method('resourcesList')
            ->willReturn($resourcesList);

        /*
         * Expect to retrieve the file content by using WordPress function
         * wp_safe_remote_post.
         */
        expect('wp_safe_remote_post')
            ->once()
            ->with($remoteResourcePath)
            ->andReturn($httpResponse);

        /*
         * Then expect to extract the body from the http response
         */
        expect('wp_remote_retrieve_body')
            ->once()
            ->with($httpResponse)
            ->andReturn($fileContent);

        /*
         * Expect to store the file content
         */
        $testee
            ->expects($this->never())
            ->method('storeFileContent');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $resourceDictionary, $baseLocalPath);
    }

    /* -------------------------------------------------------------------
       Test storeFileContent
       ---------------------------------------------------------------- */

    /**
     * Test storeFileContent
     */
    public function testStoreFileContent()
    {
        /*
         * Stubs
         */
        $localFilePath = uniqid();
        $fileContent = uniqid();
        $baseLocalPath = uniqid();
        $expectedLocalPath = "{$baseLocalPath}/{$localFilePath}";

        /*
         * Setup Dependencies
         */
        $wpFileSystem = $this
            ->getMockBuilder('\\WP_Filesystem_Base')
            ->setMethods(['exists', 'delete', 'put_contents'])
            ->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$wpFileSystem],
            'storeFileContent',
            []
        );

        /*
         * Expect to check if the file already exists and remove it before
         * store the content into the file.
         */
        $wpFileSystem
            ->expects($this->once())
            ->method('exists')
            ->with($expectedLocalPath)
            ->willReturn(true);

        $wpFileSystem
            ->expects($this->once())
            ->method('delete')
            ->with($expectedLocalPath)
            ->willReturn(true);

        $wpFileSystem
            ->expects($this->once())
            ->method('put_contents')
            ->with($expectedLocalPath, $fileContent, FS_CHMOD_FILE)
            ->willReturn(true);

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $expectedLocalPath, $fileContent);
    }

    /**
     * Test file content isn't stored because existing file cannot be deleted
     */
    public function testStoreFileContentFailBecauseExistingFileCannotBeDeleted()
    {
        /*
         * Stubs
         */
        $localFilePath = uniqid();
        $fileContent = uniqid();
        $baseLocalPath = uniqid();
        $expectedLocalPath = "{$baseLocalPath}/{$localFilePath}";

        /*
         * Setup Dependencies
         */
        $wpFileSystem = $this
            ->getMockBuilder('\\WP_Filesystem_Base')
            ->setMethods(['exists', 'delete', 'put_contents'])
            ->getMock();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [$wpFileSystem],
            'storeFileContent',
            []
        );

        /*
         * Expect to check if the file already exists and remove it before
         * store the content into the file.
         */
        $wpFileSystem
            ->expects($this->once())
            ->method('exists')
            ->with($expectedLocalPath)
            ->willReturn(true);

        $wpFileSystem
            ->expects($this->once())
            ->method('delete')
            ->with($expectedLocalPath)
            ->willReturn(false);

        $wpFileSystem
            ->expects($this->never())
            ->method('put_contents');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $expectedLocalPath, $fileContent);
    }
}
