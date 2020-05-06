<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Install;

use WCPayPalPlus\Http\PayPalAssetsCache\AssetsStoreUpdater;
use WCPayPalPlus\Setting\SharedPersistor;

/**
 * Class Installer
 * @package WCPayPalPlus\Installation
 */
class Installer
{
    const ORIGINAL_OPTIONS = 'woocommerce_paypal_plus_settings';

    /**
     * @var SharedPersistor
     */
    private $sharedPersistor;

    /**
     * @var AssetsStoreUpdater
     */
    private $assetsStoreUpdater;

    /**
     * Installer constructor.
     * @param SharedPersistor $sharedPersistor
     * @param AssetsStoreUpdater|null  $assetsStoreUpdater
     */
    public function __construct(
        SharedPersistor $sharedPersistor,
        $assetsStoreUpdater
    ) {

        $this->sharedPersistor = $sharedPersistor;
        if ($assetsStoreUpdater) {
            $this->assetsStoreUpdater = $assetsStoreUpdater;
        }
    }

    /**
     * Perform Tasks After Plugin is Installed or Upgraded
     */
    public function afterInstall()
    {
        $this->migrateSharedOptions();
        if ($this->assetsStoreUpdater) {
            $this->assetsStoreUpdater->update();
        }
    }

    /**
     * Migrate Shared options
     *
     * @return void
     */
    private function migrateSharedOptions()
    {
        $options = get_option(self::ORIGINAL_OPTIONS, []);

        if (!$options) {
            return;
        }

        $this->sharedPersistor->update($options);
    }
}
