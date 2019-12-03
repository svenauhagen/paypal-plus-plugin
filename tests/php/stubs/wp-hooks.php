<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('apply_filters')) {
    function apply_filters($filter, ...$args)
    {
        $container = \Brain\Monkey\Container::instance();
        $container->hookStorage()->pushToDone(
            \Brain\Monkey\Hook\HookStorage::FILTERS,
            $filter,
            $args
        );

        $return = $container->hookExpectationExecutor()->executeApplyFilters($filter, $args);

        $isCallbackArgument = is_callable(isset($args[0]) ? $args[0] : false);
        $arguments = array_slice($args, 1);

        $isCallbackArgument and $return = $args[0](...$arguments);

        return $return;
    }
}
