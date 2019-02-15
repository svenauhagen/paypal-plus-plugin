<?php
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\WC\PUI;

/**
 * Class PaymentInstructionDataFactory
 * @package WCPayPalPlus\WC\PUI
 */
class PaymentInstructionFactory
{
    public static function createData(\WC_Order $order, $legalNote)
    {
        assert(is_string($legalNote));

        return new PaymentInstructionData($order, $legalNote);
    }

    public static function createViewFromData(PaymentInstructionData $data)
    {
        return new PaymentInstructionView($data);
    }
}
