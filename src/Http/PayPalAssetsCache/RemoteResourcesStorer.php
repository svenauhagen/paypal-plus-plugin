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

use WP_Filesystem_Base;

/**
 * Class RemoteResourcesStorer
 * @package WCPayPalPlus\Http\PayPalAssetsCache
 */
class RemoteResourcesStorer
{
    /**
     * @var WP_Filesystem_Base
     */
    private $fileSystem;

    /**
     * ResourcesDownloader constructor.
     * @param WP_Filesystem_Base $fileSystem
     */
    public function __construct(WP_Filesystem_Base $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Update Resources
     *
     * @param ResourceDictionary $resourceDictionary
     * @param $baseLocalPath
     * @return void
     */
    public function update(ResourceDictionary $resourceDictionary, $baseLocalPath)
    {
        assert(is_string($baseLocalPath) && !empty($baseLocalPath));

        $resourceDictionaryList = $resourceDictionary->resourcesList();

        if (!$resourceDictionaryList) {
            return;
        }

        foreach ($resourceDictionaryList as $localFilePath => $remoteFilePath) {
            $response = wp_safe_remote_post($remoteFilePath);
            $fileContent = wp_remote_retrieve_body($response);

            if (!$fileContent) {
                continue;
            }

            $storeFilePath = trailingslashit($baseLocalPath) . ltrim($localFilePath, '/');

            $this->storeFileContent($storeFilePath, $fileContent);
        }
    }

    /**
     * Store File Content Locally
     *
     * @param $filePath
     * @param $fileContent
     * @return void
     */
    protected function storeFileContent($filePath, $fileContent)
    {
        assert(is_string($filePath) && !empty($filePath));
        assert(is_string($fileContent) && !empty($fileContent));

        $fileExists = $this->fileSystem->exists($filePath);
        $deleted = false;

        if ($fileExists) {
            $deleted = $this->fileSystem->delete($filePath);
        }

        if ($fileExists && !$deleted) {
            return;
        }

        $this->fileSystem->put_contents($filePath, $fileContent, FS_CHMOD_FILE);
    }
}
