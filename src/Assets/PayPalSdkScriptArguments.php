<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Assets;

/**
 * Class PayPalSdkArguments
 * @package WCPayPalPlus\Assets
 */
class PayPalSdkScriptArguments
{
    const FILTER_LOCALE = 'woopaypalplus.express_checkout_button_locale';
    const DISABLED_FUNDING = [
        'card',
        'credit',
        'sepa',
    ];
    const DISABLED_CARDS = [
        'visa',
        'mastercard',
        'amex',
        'discover',
        'jcb',
        'elo',
        'hiper',
    ];

    /**
     * Return the Script Arguments as an array
     *
     * @return array
     */
    public function toArray()
    {
        $currency = $this->wooCommerceSettings('currency', 'EUR');
        $locale = get_locale();

        /**
         * Filter locale
         *
         * Allow third parties to filter the locale if needed.
         *
         * @param string $locale
         */
        $locale = apply_filters(self::FILTER_LOCALE, $locale);

        return [
            'currency' => $currency,
            // TODO Need correct client ID value. Consider also to ensure the token.
            'client-id' => 'sb',
            'locale' => $locale,
            // TODO Reactivate when we'll have a valid client-id
//            'disable-card' => $this->reduceArrayToValueList(self::DISABLED_CARDS),
//            'disable-funding' => $this->reduceArrayToValueList(self::DISABLED_FUNDING),
        ];
    }

    /**
     * Retrieve a WooCommerce Option by the given name
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function wooCommerceSettings($name, $default = null)
    {
        assert(is_string($name));

        return \WC_Admin_Settings::get_option($name, $default);
    }

    /**
     * Convert array to a list of values separated by comma
     *
     * @param $array
     * @return array
     */
    private function reduceArrayToValueList($array)
    {
        assert(is_array($array));

        return implode(',', $array);
    }
}
