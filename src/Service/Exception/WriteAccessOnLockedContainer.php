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
 * Exception to be thrown when a locked container is to be manipulated.
 */
class WriteAccessOnLockedContainer extends NameOverwriteNotAllowed
{
    /**
     * @param string $name
     * @return WriteAccessOnLockedContainer
     */
    public static function forName($name)
    {
        assert(is_string($name));

        return new static(
            "Cannot access {$name} for writing. Manipulating a locked container is not allowed."
        );
    }
}
