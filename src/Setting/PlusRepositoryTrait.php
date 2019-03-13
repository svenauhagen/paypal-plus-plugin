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
 * Class PlusRepositoryHelper
 * @package WCPayPalPlus\Setting
 */
trait PlusRepositoryTrait
{
    /**
     * @inheritdoc
     */
    public function isDisableGatewayOverrideEnabled()
    {
        $option = $this->get_option(self::OPTION_DISABLE_GATEWAY_OVERRIDE_NAME, self::OPTION_OFF);

        return $option === self::OPTION_ON;
    }

    /**
     * @inheritdoc
     */
    public function legalNotes()
    {
        return $this->get_option(PlusStorable::OPTION_LEGAL_NOTE_NAME, '');
    }

    /**
     * @inheritdoc
     */
    public function invoicePrefix()
    {
        return $this->get_option(PlusStorable::OPTION_INVOICE_PREFIX, '');
    }
}
