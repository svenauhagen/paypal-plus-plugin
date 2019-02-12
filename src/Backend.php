<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 20.10.16
 * Time: 14:42
 */

namespace WCPayPalPlus;

use WCPayPalPlus\WC\PayPalPlusGateway;

/**
 * Class Backend
 *
 * @package WCPayPalPlus
 */
final class Backend implements Controller
{
    /**
     * @var PayPalPlusGateway
     */
    private $gateway;

    /**
     * @var string
     */
    private $file;

    public function __construct($file, PayPalPlusGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->file = $file;
    }

    public function init()
    {
        add_action('admin_enqueue_scripts', function () {
            $assetUrl = plugin_dir_url($this->file);
            $min = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
            $adminScript = "{$assetUrl}/assets/js/admin{$min}.js";
            $adminStyle = "{$assetUrl}/assets/css/admin{$min}.css";

            wp_enqueue_script('paypalplus-woocommerce-admin', $adminScript, ['jquery']);
            wp_enqueue_style('paypalplus-woocommerce-admin', $adminStyle, []);
        });
    }
}
