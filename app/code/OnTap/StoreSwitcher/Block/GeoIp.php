<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\StoreSwitcher\Block;

use Magento\Framework\View\Element\Template;

class GeoIp extends Template
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteCode()
    {
        return strtolower($this->_storeManager->getWebsite()->getCode());
    }
}
