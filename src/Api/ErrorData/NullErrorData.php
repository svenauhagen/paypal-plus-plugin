<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Api\ErrorData;

/**
 * Class NullErrorData
 * @package WCPayPalPlus\Api
 */
final class NullErrorData implements ErrorData
{
    /**
     * @inheritdoc
     */
    public function code()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function details()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function message()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function debugId()
    {
        return '';
    }
}
