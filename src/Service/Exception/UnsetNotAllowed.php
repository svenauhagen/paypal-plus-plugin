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
class UnsetNotAllowed extends InvalidValueAccess
{
    /**
     * @param string $name
     * @return UnsetNotAllowed
     */
    public static function forName($name)
    {
        assert(is_string($name));

        return new static("Cannot unset {$name}. Removing items from container is not allowed.");
    }
}
