<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus;

use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\Exception\NameNotFound;

/**
 *  * Resolves the value with the given name from the container.
 *
 *
 * @param string $name
 * @return mixed
 * @throws NameNotFound
 * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
 */
function resolve($name = '')
{
    // phpcs:enable

    assert(is_string($name));

    static $container;
    $container or $container = new Container();

    if ($name && !isset($container[$name])) {
        throw NameNotFound::forName($name);
    }

    return $name ? $container[$name] : $container;
}

/**
 * Check if Given Gateway is Available or not
 *
 * @param $gateway
 * @return bool
 */
function isGatewayDisabled($gateway)
{
    return ($gateway->enabled !== 'yes' && !is_admin());
}
