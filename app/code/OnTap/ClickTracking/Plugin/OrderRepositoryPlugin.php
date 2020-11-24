<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ClickTracking\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use OnTap\ClickTracking\Model\Tracking;

class OrderRepositoryPlugin
{
    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $result
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $result
    ) {
        return $this->_afterGet($result);
    }

    /**
     * @param OrderInterface $result
     */
    protected function _afterGet(
        OrderInterface $result
    ) {
        $extensionAttributes = $result->getExtensionAttributes();
        if ($extensionAttributes) {
            $extensionAttributes->setGclid($result->getData(Tracking::GCLID));
            $extensionAttributes->setMsclkid($result->getData(Tracking::MSCLKID));
            $extensionAttributes->setIto($result->getData(Tracking::ITO));
        }
        return $result;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ) {
        foreach ($searchResult->getItems() as &$order) {
            $this->_afterGet($order);
        }
        return $searchResult;
    }
}
