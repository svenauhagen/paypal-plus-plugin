<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the PayPal PLUS for WooCommerce package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WCPayPalPlus\Request;

/**
 * Class Request
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
    const KEY_PAYMENT_METHOD = 'payment_method';

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
     * @param $filter
     * @param null $options
     * @return mixed
     */
    public function get($name, $filter, $options = null)
    {
        if (!$this->has($name)) {
            return null;
        }

        $value = $this->request[$name];

        if ($name === self::KEY_PAYMENT_STATUS) {
            $value = strtolower($value);
        }

        return filter_var($value, $filter, $options);
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
