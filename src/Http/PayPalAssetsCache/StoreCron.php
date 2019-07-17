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
     * @var RemoteResourcesStorer
     */
    private $remoteResourcesStorer;

    /**
     * StorerCron constructor.
     * @param RemoteResourcesStorer $remoteResourcesStorer
     * @param ResourceDictionary $resourceDictionary
     */
    public function __construct(
        RemoteResourcesStorer $remoteResourcesStorer,
        ResourceDictionary $resourceDictionary
    ) {

        $this->remoteResourcesStorer = $remoteResourcesStorer;
        $this->resourceDictionary = $resourceDictionary;
    }

    /**
     * Execute Cron Event
     */
    public function execute()
    {
        $this->remoteResourcesStorer->update($this->resourceDictionary);
    }
}
