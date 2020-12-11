<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 */

namespace WCPayPalPlus\Order;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use WC_Order;
use WCPayPalPlus\Request\Request;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;
use WCPayPalPlus\Session\Session;
use WCPayPalPlus\Setting\Storable;
use WooCommerce;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Order
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container[OrderStatuses::class] = function () {
            return new OrderStatuses();
        };
        $container[OrderFactory::class] = function () {
            return new OrderFactory();
        };
        $container[OrderUpdaterFactory::class] = function (Container $container) {
            return new OrderUpdaterFactory(
                $container[WooCommerce::class],
                $container[OrderStatuses::class],
                $container[OrderFactory::class],
                $container[Request::class],
                $container[Storable::class],
                $container[Logger::class]
            );
        };
        $container[OrderDataProviderFactory::class] = function (Container $container) {
            return new OrderDataProviderFactory(
                $container[OrderFactory::class],
                $container[Session::class],
                $container[WooCommerce::class]
            );
        };
    }

    public function bootstrap(Container $container)
    {
        $session = $container[Session::class];

        add_action('wp_loaded', function () use ($session) {

            $cancelOrder = filter_input(INPUT_GET, 'cancel_order', FILTER_SANITIZE_STRING);
            if (isset($cancelOrder)) {
                $orderId = $session->get(Session::ORDER_ID);
                $order = wc_get_order($orderId);

                if ($order && $this->orderIsCancelable($order, $orderId)) {
                    $order->update_status(
                        'cancelled',
                        __('Order cancelled by customer.', 'woo-paypalplus')
                    );

                    wc_add_notice(__('Your order was cancelled.', 'woo-paypalplus'), 'notice');
                }
            }
        });
    }

    /**
     * @param WC_Order $order
     * @param int $orderId
     * @return bool
     */
    protected function orderIsCancelable($order, $orderId)
    {
        $nonce = filter_input(INPUT_GET, 'nonce', FILTER_SANITIZE_STRING);

        return isset($nonce)
            && wp_verify_nonce(wp_unslash($nonce), 'cancel-order')
            && $order->has_status(['pending', 'failed'])
            && current_user_can('cancel_order', $orderId);
    }
}
