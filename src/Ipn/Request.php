<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Ipn;

/**
 * Class IpnRequest
 *
 * @method custom
 * @method paymentStatus
 *
 * @package WCPayPalPlus\Ipn
 */
class Request
{
    const KEY_MC_FEE = 'mc_fee';
    const KEY_TXN_ID = 'txn_id';
    const KEY_CUSTOM = 'custom';
    const KEY_PENDING_REASON = 'pending_reason';
    const KEY_PAYMENT_STATUS = 'payment_status';

    /**
     * Request data
     *
     * @var array
     */
    private $request;

    /**
     * Data constructor.
     * @param array $request
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Returns all request data
     *
     * @return array
     */
    public function all()
    {
        return $this->request;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            return null;
        }

        $value = $this->request[$name];

        if ($name === self::KEY_PAYMENT_STATUS) {
            $value = strtolower($value);
        }

        return $value;
    }

    /**
     * Checks if a specific value exists
     *
     * @param string $offset The key to search.
     *
     * @return bool
     */
    public function has($offset)
    {
        return isset($this->request[$offset]);
    }
}
