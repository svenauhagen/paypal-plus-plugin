<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Ipn;

use WCPayPalPlus\Setting;

/**
 * Class IPNData
 *
 * TODO Data could be removed in favor of moving methods into the settings Repository.
 *
 * @package WCPayPalPlus\Ipn
 */
class Data
{
    const PAYPAL_SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    const PAYPAL_LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * URL to use for PayPal calls
     *
     * @var string
     */
    private $paypal_url;

    /**
     * @var Setting\Storable
     */
    private $settingRepository;

    /**
     * Data constructor.
     * @param Setting\Storable $settingRepository
     */
    public function __construct(Setting\Storable $settingRepository)
    {
        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->settingRepository = $settingRepository;
        $this->paypal_url = $this->settingRepository->isSandboxed()
            ? self::PAYPAL_SANDBOX_URL
            : self::PAYPAL_LIVE_URL;
    }

    /**
     * Returns the URL to the PayPal service
     *
     * @return string
     */
    public function paypalUrl()
    {
        return $this->paypal_url;
    }

    /**
     * Returns the UA to use in PayPal calls
     *
     * @return string
     */
    public function userAgent()
    {
        return 'WooCommerce/' . wc()->version;
    }
}
