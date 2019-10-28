<?php # -*- coding: utf-8 -*-

/*
 * This file is part of the PayPal PLUS package.
 *
 * (c) Inpsyde <hello@inpsyde.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$vendor = dirname(__DIR__, 2) . '/vendor/';
if (!file_exists($vendor . 'autoload.php')) {
    die('Please install via Composer before running tests.');
}

require_once $vendor . 'brain/monkey/inc/patchwork-loader.php';
require_once $vendor . 'autoload.php';
require_once __DIR__ . '/stubs/wp-hooks.php';
unset($vendor);

putenv('PROJECT_DIR=' . dirname(__DIR__, 2));
putenv('TESTS_PATH=' . __DIR__);

// Define Constants
if (!defined('FS_CHMOD_FILE')) {
    define('FS_CHMOD_FILE', 0777 | 0644);
}

if (!defined('FS_CHMOD_DIR')) {
    define('FS_CHMOD_DIR', 0777 | 0755);
}

if (!defined('DAY_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
    define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
    define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
    define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
    define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
    define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);
}
