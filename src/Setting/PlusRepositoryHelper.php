<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Setting;

/**
 * Class PlusRepositoryHelper
 * @package WCPayPalPlus\Setting
 */
trait PlusRepositoryHelper
{
    /**
     * @inheritdoc
     */
    public function isDefaultGatewayOverrideEnabled()
    {
        $option = $this->get_option(self::OPTION_DISABLE_GATEWAY_OVERRIDE_NAME, self::OPTION_OFF);

        return $option === self::OPTION_ON;
    }

    /**
     * @inheritdoc
     */
    public function isSandboxed()
    {
        $option = $this->get_option(self::OPTION_TEST_MODE_NAME, self::OPTION_ON);

        return $option === self::OPTION_ON;
    }

    /**
     * @inheritdoc
     */
    public function legalNotes()
    {
        return $this->get_option('legal_note', '');
    }

    /**
     * @inheritdoc
     */
    public function experienceProfileId()
    {
        $option = $this->isSandboxed()
            ? PlusStorable::OPTION_PROFILE_ID_SANDBOX_NAME
            : PlusStorable::OPTION_PROFILE_ID_LIVE_NAME;

        return $this->get_option($option, '');
    }

    /**
     * @inheritdoc
     */
    public function cancelUrl()
    {
        $option = $this->get_option(PlusStorable::OPTION_CANCEL_URL_NAME, '');

        switch ($option) {
            case 'cart':
                $url = wc_get_cart_url();
                break;
            case 'checkout':
                $url = wc_get_checkout_url();
                break;
            case 'account':
                $url = wc_get_account_endpoint_url('dashboard');
                break;
            case 'custom':
                $url = esc_url($this->cancelCustomUrl());
                break;
            case 'shop':
            default:
                $url = get_permalink(wc_get_page_id('shop'));
                break;
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function cancelCustomUrl()
    {
        return $this->get_option(PlusStorable::OPTION_CANCEL_CUSTOM_URL_NAME, '');
    }

    /**
     * @inheritdoc
     */
    public function invoicePrefix()
    {
        return $this->get_option('invoice_prefix', '');
    }

    /**
     * @inheritdoc
     */
    public function paypalUrl()
    {
        return $this->isSandboxed() ? self::PAYPAL_SANDBOX_URL : self::PAYPAL_LIVE_URL;
    }

    /**
     * @inheritdoc
     */
    public function userAgent()
    {
        return 'WooCommerce/' . $this->wooCommerce->version;
    }
}
