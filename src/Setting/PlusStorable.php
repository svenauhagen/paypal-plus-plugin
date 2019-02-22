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

interface PlusStorable extends Storable
{
    const OPTION_DISABLE_GATEWAY_OVERRIDE_NAME = 'disable_gateway_override';
    const OPTION_TEST_MODE_NAME = 'testmode';
    const OPTION_LEGAL_NOTE_NAME = 'legal_note';

    /**
     * @return bool
     */
    public function isDefaultGatewayOverrideEnabled();

    /**
     * @return mixed
     */
    public function legalNotes();
}
