<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Refund;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WCPayPalPlus\Api\ErrorData\ApiErrorDataExtractor;
use WCPayPalPlus\Order\OrderStatuses;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Service\ServiceProvider as ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Refund
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[RefundFactory::class] = function (Container $container) {
            return new RefundFactory(
                $container[OrderStatuses::class],
                $container[Logger::class]
            );
        };
    }
}
