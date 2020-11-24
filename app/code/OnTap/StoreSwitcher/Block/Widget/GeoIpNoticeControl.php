<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\StoreSwitcher\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Serialize\Serializer\Json;

class GeoIpNoticeControl extends \Magento\Framework\View\Element\Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'OnTap_StoreSwitcher::widget/geoip_control.phtml';

    /**
     * @return array
     */
    public function getRegionOptions()
    {
        $options = [];
        foreach ($this->_storeManager->getStores() as $store) {
            $website = $this->_storeManager->getWebsite($store->getWebsiteId());
            $options[] = [
                'label' => __('%1 (%2)', $website->getName(), $store->getName()),
                'value' => $store->getBaseUrl(),
                'selected' => strtolower($store->getCode()) === strtolower($this->getData('selected_country_id'))
            ];
        }
        return $options;
    }
}
