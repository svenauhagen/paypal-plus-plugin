<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Session;

use WC_Session_Handler;
use OutOfBoundsException;
use WooCommerce;

/**
 * Class Session
 * @package WCPayPalPlus\Payment
 */
class WooCommerceSession implements Session
{
    /**
     * @var WooCommerce
     */
    private $wooCommerce;

    /**
     * Session constructor.
     * @param WooCommerce $wooCommerce
     */
    public function __construct(WooCommerce $wooCommerce)
    {
        $this->wooCommerce = $wooCommerce;
    }

    /**
     * @param $name
     * @return array|string
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            return self::DEFAULT_VALUE;
        }

        return $this->session()->get($name);
    }

    /**
     * @param $name
     * @param $value
     * @throws OutOfBoundsException
     */
    public function set($name, $value)
    {
        if (!in_array($name, self::ALLOWED_PROPERTIES, true)) {
            throw new OutOfBoundsException("Cannot set unknown property {$name}");
        }

        $this->session()->set($name, $value);
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return $this->session()->__isset($name);
    }

    /**
     * Delete all of the properties and their values from the session storage
     */
    public function clean()
    {
        foreach (self::ALLOWED_PROPERTIES as $property) {
            $this->session()->__unset($property);
        }
    }

    /**
     * Lazy load the session because WooCommerce set the session during init hook
     *
     * @return WC_Session_Handler
     *
     * phpcs:disable Generic.NamingConventions.ConstructorName.OldStyle
     */
    private function session()
    {
        // phpcs:enable

        if (!did_action('init')) {
            _doing_it_wrong(__METHOD__, 'Cannot be called before WordPress init.', '2.0.0');
        }

        static $session = null;

        if ($session !== null) {
            return $session;
        }

        $session = $this->wooCommerce->session;

        return $session;
    }
}
