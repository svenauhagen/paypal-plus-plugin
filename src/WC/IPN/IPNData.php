<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 07.12.16
 * Time: 16:43
 */

namespace WCPayPalPlus\WC\IPN;

/**
 * Class IPNData
 *
 * @package WCPayPalPlus\WC\IPN
 */
class IPNData
{
    const PAYPAL_SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    const PAYPAL_LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * Request data
     *
     * @var array
     */
    private $request;

    /**
     * URL to use for PayPal calls
     *
     * @var string
     */
    private $paypal_url;

    /**
     * Order update handler
     *
     * @var OrderUpdater
     */
    private $updater;

    /**
     * IPNData constructor.
     *
     * @param array $request Request data.
     * @param bool $sandbox Flag to set sandbox mode.
     */
    public function __construct(array $request = [], $sandbox = true)
    {
        $this->request = $request;
        $this->paypal_url = $sandbox ? self::PAYPAL_SANDBOX_URL : self::PAYPAL_LIVE_URL;
    }

    /**
     * Returns the URL to the PayPal service
     *
     * @return string
     */
    public function paypalUrl()
    {
        return $this->paypal_url;
    }

    /**
     * Returns the current status
     *
     * @return string
     */
    public function paymentStatus()
    {
        return strtolower($this->get('payment_status'));
    }

    /**
     * Fetches a specific value by key
     *
     * @param string $offset The key to fetch from data.
     * @param string $fallback Fallback value to return if the value wasn't found.
     *
     * @return mixed|string
     */
    public function get($offset, $fallback = '')
    {
        if ($this->has($offset)) {
            return $this->request[$offset];
        }

        return $fallback;
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

    /**
     * Returns the Order Update handler
     *
     * @return OrderUpdater
     */
    public function orderUpdater()
    {
        if ($this->updater === null) {
            $this->updater = new OrderUpdater($this->woocommerceOrder(), $this);
        }

        return $this->updater;
    }

    /**
     * Get the order from the PayPal 'Custom' variable.
     * TODO Passing and decoding custom data should by handled by one set of objects.
     * TODO Right now, this is implemented in 2 completely separate places
     *
     * @return \WC_Order
     */
    public function woocommerceOrder()
    {
        $raw_custom = $this->get('custom', false);
        if (!$raw_custom) {
            return null;
        }

        $custom = json_decode($raw_custom);
        if ($custom && is_object($custom)) {
            $order_id = $custom->order_id;
            $order_key = $custom->order_key;
        } else {
            return null;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            $order_id = wc_get_order_id_by_order_key($order_key);
            $order = wc_get_order($order_id);
        }
        if (!$order) {
            return null;
        }

        return $order;
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
     * Returns the UA to use in PayPal calls
     *
     * @return string
     */
    public function userAgent()
    {
        return 'WooCommerce/' . WC()->version;
    }
}
