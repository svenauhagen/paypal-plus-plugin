<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Service\Exception;

/**
 * Exception to be thrown when a value that has already been set is to be manipulated.
 */
class NameOverwriteNotAllowed extends InvalidValueAccess
{
    /**
     * @param string $name
     * @return NameOverwriteNotAllowed
     */
    public static function forServiceName($name)
    {
        assert(is_string($name));

        return new static(
            "Cannot set a service with name '{$name}'. A service with this name already exists."
        );
    }

    /**
     * @param string $name
     * @return NameOverwriteNotAllowed
     */
    public static function forValueName($name)
    {
        assert(is_string($name));

        return new static(
            "Cannot set a value with name '{$name}'. A value with this name already exists."
        );
    }
}
