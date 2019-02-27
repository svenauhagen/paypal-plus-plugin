<?php
/**
 * Created by PhpStorm.
 * User: biont
 * Date: 03.11.16
 * Time: 14:27
 */

namespace WCPayPalPlus\WC;

/**
 * Class PayPalIframeView
 *
 * @package WCPayPalPlus\WC
 */
class PlusFrameView
{
    /**
     * Render the Paywall iframe
     */
    public function render(array $data)
    {
        $id = $data['placeholder'];
        $config = wp_json_encode($data);
        ?>
        <div
            id="<?php echo esc_attr($id) ?>"
            class="paypalplus-paywall"
            data-config="<?= esc_attr($config) ?>"
        ></div>
        <?php
    }
}
