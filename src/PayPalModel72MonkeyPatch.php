<?php

namespace WCPayPalPlus;

/**
 * Class PayPalModel72MonkeyPatch
 * @package WCPayPalPlus
 *
 * Actually we have a problem in php 7.2 because of the sizeof function could be called with
 * a parameter that doesn't implement the `Countable` interface.
 * This class was introduced as a workaround
 *
 * @link https://github.com/paypal/PayPal-PHP-SDK/issues/1014
 */
class PayPalModel72MonkeyPatch
{
    const WARNING_MESSAGE = 'sizeof(): Parameter must be an array or an object that implements Countable';
    const WARNING_FILE = 'PayPalModel.php';
    const LEVELS = E_WARNING;

    public function setHandler()
    {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
        set_error_handler(function ($errno, $errstring, $errfile) {
            return $this->isSizeOfModelWarning($errstring, $errfile);
        }, self::LEVELS);
    }

    private function isSizeOfModelWarning($errstring, $errfile)
    {
        $messageMatch = strpos($errstring, self::WARNING_MESSAGE);
        $fileMatch = strpos($errfile, self::WARNING_FILE);

        return !in_array(false, [$messageMatch, $fileMatch], true);
    }
}
