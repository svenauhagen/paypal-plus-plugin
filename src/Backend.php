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
class Backend implements Controller
{
    /**
     * Gateway class
     *
     * @var PayPalPlusGateway
     */
    private $gateway;

    /**
     * Main Plugin file path
     *
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $assetsUrl;

    /**
     * @var string
     */
    private $assetsPath;

    public function __construct($file, PayPalPlusGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->file = $file;
        $this->assetsPath = untrailingslashit(plugin_dir_path($this->file));
        $this->assetsUrl = untrailingslashit(plugin_dir_url($this->file));
    }

    public function init()
    {
        add_action('admin_enqueue_scripts', function () {
            $this->enqueueScripts();
        });
    }

    private function enqueueScripts()
    {
        $suffix = $this->productionSuffix();
        $fileUrl = "{$this->assetsUrl}/assets/js/admin{$suffix}.js";
        wp_enqueue_script(
            'paypalplus-woocommerce-admin',
            $fileUrl,
            ['jquery'],
            $this->filemtime($fileUrl),
            true
        );
    }

    private function productionSuffix()
    {
        return (defined('SCRIPT_DEBUG') and SCRIPT_DEBUG) ? '.min' : '';
    }

    private function filemtime($file)
    {
        $filePath = str_replace(
            $this->assetsUrl,
            $this->assetsPath,
            $file
        );

        return filemtime($filePath);
    }
}
