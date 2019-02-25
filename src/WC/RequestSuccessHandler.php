<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 02.12.16
 * Time: 10:55
 */

namespace WCPayPalPlus\WC;

interface RequestSuccessHandler
{
    /**
     * Handles a successful REST call
     *
     * @return bool
     */
    public function execute();
}
