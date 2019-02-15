<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Pui;

/**
 * Class PaymentInstructionDataFactory
 * @package WCPayPalPlus\Pui
 */
class Factory
{
    public static function createData(\WC_Order $order, $legalNote)
    {
        assert(is_string($legalNote));

        return new Data($order, $legalNote);
    }

    public static function createViewFromData(Data $data)
    {
        return new View($data);
    }
}
