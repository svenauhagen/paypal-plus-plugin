<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Setting;

/**
 * Interface PlusStorable
 * @package WCPayPalPlus\Setting
 */
interface PlusStorable extends Storable
{
    // TODO Remove the _NAME suffix, it's additional information unneeded.
    const OPTION_DISABLE_GATEWAY_OVERRIDE_NAME = 'disable_gateway_override';
    const OPTION_LEGAL_NOTE_NAME = 'legal_note';

    const OPTION_INVOICE_PREFIX = 'invoice_prefix';

    /**
     * @return bool
     */
    public function isDisableGatewayOverrideEnabled();

    /**
     * @return string
     */
    public function legalNotes();

    /**
     * @return string
     */
    public function invoicePrefix();
}
