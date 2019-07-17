<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Http;

use Inpsyde\Lib\Psr\Log\LoggerInterface as Logger;
use UnexpectedValueException;
use WCPayPalPlus\Http\PayPalAssetsCache\CronScheduler;
use WCPayPalPlus\Http\PayPalAssetsCache\RemoteResourcesStorer;
use WCPayPalPlus\Http\PayPalAssetsCache\ResourceDictionary;
use WCPayPalPlus\Http\PayPalAssetsCache\StoreCron;
use WCPayPalPlus\Service\BootstrappableServiceProvider;
use WCPayPalPlus\Service\Container;

/**
 * Class ServiceProvider
 * @package WCPayPalPlus\Http
 */
class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        $uploadDir = wp_upload_dir();
        $uploadDir = isset($uploadDir['basedir']) ? $uploadDir['basedir'] : '';

        if (!$uploadDir) {
            return;
        }

        try {
            $fileSystem = $container->get('wp_filesystem');
        } catch (UnexpectedValueException $exc) {
            $container->get(Logger::class)->warning($exc->getMessage());
            return;
        }

        $container->addService(
            CronScheduler::class,
            function () {
                return new CronScheduler();
            }
        );

        $container->addService(
            RemoteResourcesStorer::class,
            function () use ($fileSystem) {
                return new RemoteResourcesStorer($fileSystem);
            }
        );

        $container->addService(
            ResourceDictionary::class,
            function () {
                return new ResourceDictionary(
                    [
                        'woo-paypalplus/resources/js/paypal/expressCheckout.min.js' => 'https://www.paypalobjects.com/api/checkout.min.js',
                        'woo-paypalplus/resources/js/paypal/payPalplus.min.js' => 'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js',
                    ]
                );
            }
        );

        $container->addService(
            StoreCron::class,
            function (Container $container) {
                return new StoreCron(
                    $container->get(RemoteResourcesStorer::class),
                    $container->get(ResourceDictionary::class)
                );
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function bootstrap(Container $container)
    {
        $cronScheduler = $container->get(CronScheduler::class);

        add_filter(
            'cron_schedules',
            function (array $schedules) use ($cronScheduler) {
                return $cronScheduler->addWeeklyRecurrence($schedules);
            }
        );

        $cronScheduler->schedule();

        add_action(CronScheduler::CRON_HOOK_NAME, [$container->get(StoreCron::class), 'execute']);
    }
}
