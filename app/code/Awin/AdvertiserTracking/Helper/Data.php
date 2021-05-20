<?php

namespace Awin\AdvertiserTracking\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getAdvertiserId()
    {
        try {
            return $this->scopeConfig->getValue('awin_settings/general/awin_advertiser_id', \Magento\Store\Model\ScopeInterface:: SCOPE_STORE);
        } catch (Exception $e) {
            return null;
        }
    }    
}