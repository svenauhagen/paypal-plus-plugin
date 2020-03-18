<?php


namespace WCPayPalPlus\Banner;

use WCPayPalPlus\Setting\SharedRepository;

class BannerSdkScriptUrl
{
    /**
     * @var SharedRepository
     */
    private $sharedRepository;

    /**
     * BannerSdkScriptUrl constructor.
     *
     * @param SharedRepository $sharedRepository
     */
    public function __construct(SharedRepository $sharedRepository)
    {
        $this->sharedRepository = $sharedRepository;
    }

    public function paypalScriptUrl()
    {
        $clientId = get_option('banner_settings_clientID');
        if (empty($clientId)) {
            $clientId = $this->sharedRepository->clientIdProduction();
            update_option('banner_settings_clientID', $clientId);
        }
        $currency = get_woocommerce_currency();
        if (!isset($clientId) || !isset($currency)) {
            return '';
        }

        return "https://www.paypal.com/sdk/js?client-id={$clientId}&components=messages&currency={$currency}";
    }
}
