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
use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary;
use WCPayPalPlus\Http\PayPalAssetsCache\RemoteResourcesStorer as Testee;
use WCPayPalPlus\Tests\TestCase;

/**
 * Class RemoteResourcesStorerTest
 * @package WCPayPalPlus\Tests\Unit\Http\PayPalAssetsCache
 */
class RemoteResourcesStorerTest extends TestCase
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
         * - The httpResponse variable simulate a response array returned by wp_safe_remote_get
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

        $resourceDictionary = $this
            ->getMockBuilder(ResourceDictionary::class)
            ->disableOriginalConstructor()
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
         * wp_safe_remote_get.
         */
        expect('wp_safe_remote_get')
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
           ->expects($this->once())
           ->method('storeFileContent')
           ->with($localFilePath, $fileContent);

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $resourceDictionary);
    }

    /**
     * Test update not performed because empty dictionary
     */
    public function testUpdateNotPerformedBecauseEmptyDictionary()
    {
        /*
         * Stubs
         */
        $resourceDictionary = $this
            ->getMockBuilder(ResourceDictionary::class)
            ->disableOriginalConstructor()
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
        $testeeMethod->invoke($testee, $resourceDictionary);
    }

    /**
     * Test store file isn't performed because empty file content
     */
    public function testUpdateNotPerformedBecauseEmptyFileContent()
    {
        /*
         * Stubs
         *
         * - The httpResponse variable simulate a response array returned by wp_safe_remote_get
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

        $resourceDictionary = $this
            ->getMockBuilder(ResourceDictionary::class)
            ->disableOriginalConstructor()
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
         * wp_safe_remote_get.
         */
        expect('wp_safe_remote_get')
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
        $testeeMethod->invoke($testee, $resourceDictionary);
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
            ['maybeMkdir']
        );

        /*
         * Expect to check if the file already exists and remove it before
         * store the content into the file.
         */
        $wpFileSystem
            ->expects($this->once())
            ->method('exists')
            ->with($localFilePath)
            ->willReturn(true);

        $wpFileSystem
            ->expects($this->once())
            ->method('delete')
            ->with($localFilePath)
            ->willReturn(true);

        $wpFileSystem
            ->expects($this->once())
            ->method('put_contents')
            ->with($localFilePath, $fileContent, FS_CHMOD_FILE)
            ->willReturn(true);

        /*
         * Expect to create the directory
         */
        $testee
            ->expects($this->once())
            ->method('maybeMkdir');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $localFilePath, $fileContent);
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
            ->with($localFilePath)
            ->willReturn(true);

        $wpFileSystem
            ->expects($this->once())
            ->method('delete')
            ->with($localFilePath)
            ->willReturn(false);

        $wpFileSystem
            ->expects($this->never())
            ->method('put_contents');

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $localFilePath, $fileContent);
    }

    /* -------------------------------------------------------------------
       Test maybeMkdir
       ---------------------------------------------------------------- */

    /**
     * Test maybeMkdir
     */
    public function testMaybeMkdir()
    {
        /*
         * Stubs
         */
        $path = uniqid();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'maybeMkdir',
            []
        );

        /*
         * Expect file not exists
         */
        expect('file_exists')
            ->once()
            ->with($path)
            ->andReturn(false);

        /*
         * Expect to clean the path
         */
        expect('untrailingslashit')
            ->once()
            ->with($path)
            ->andReturn($path);

        /*
         * Expect to call mkdir
         */
        expect('mkdir')
            ->once()
            ->with($path, FS_CHMOD_FILE, true);

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $path);
    }

    /**
     * Test directory is not created because it already exists
     */
    public function testMaybeMkdirDoesNotCreateDirectoryBecauseExists()
    {
        /*
         * Stubs
         */
        $path = uniqid();

        /*
         * Setup Testee
         */
        list($testee, $testeeMethod) = $this->buildTesteeMethodMock(
            Testee::class,
            [],
            'maybeMkdir',
            []
        );

        /*
         * Expect file not exists
         */
        expect('file_exists')
            ->once()
            ->with($path)
            ->andReturn(true);

        /*
         * Expect to call mkdir
         */
        expect('mkdir')->never();

        /*
         * Execute Test
         */
        $testeeMethod->invoke($testee, $path);
    }
}
