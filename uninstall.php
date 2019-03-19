<?php

namespace WCPayPalPlus;

use WCPayPalPlus\Uninstall\Uninstaller;

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$autoload = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoload)) {
    /** @noinspection PhpIncludeInspection */
    require $autoload;
}
if (!class_exists(PayPalPlus::class)) {
    return;
}

global $wpdb;

$uninstaller = new Uninstaller($wpdb);

is_multisite()
    ? $uninstaller->multisiteUninstall()
    : $uninstaller->uninstall();
