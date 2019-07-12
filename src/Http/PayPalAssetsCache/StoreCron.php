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

/**
 * Class StorerCron
 * @package WCPayPalPlus\Http\PayPalAssetsCache
 */
class StoreCron
{
    /**
     * @var ResourceDictionary
     */
    private $resourceDictionary;

    /**
     * @var string
     */
    private $baseStorePath;

    /**
     * @var RemoteResourcesStorer
     */
    private $remoteResourcesStorer;

    /**
     * StorerCron constructor.
     * @param RemoteResourcesStorer $remoteResourcesStorer
     * @param ResourceDictionary $resourceDictionary
     * @param string $baseStorePath
     */
    public function __construct(
        RemoteResourcesStorer $remoteResourcesStorer,
        ResourceDictionary $resourceDictionary,
        $baseStorePath
    ) {

        assert(is_string($baseStorePath) && !empty($baseStorePath));

        $this->remoteResourcesStorer = $remoteResourcesStorer;
        $this->resourceDictionary = $resourceDictionary;
        $this->baseStorePath = $baseStorePath;
    }

    /**
     * Execute Cron Event
     */
    public function execute()
    {
        $this->remoteResourcesStorer->update($this->resourceDictionary, $this->baseStorePath);
    }
}
