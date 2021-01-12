<?php

namespace Rvs\TaxCalculation\Plugin;

use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemExtensionInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Class OrderItemRepositoryPlugin
 */
class OrderItemRepositoryPlugin
{
    /**
        Pay atention here: 

        const CUSTOM_ATTR_NAME = 'custom_attr_name';
    */
    const CUSTOM_ATTR_NAME = 'deal_id';

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderItemExtensionFactory
     */
    protected $extensionFactory;

    /**
     * OrderItemRepositoryPlugin constructor
     *
     * @param OrderItemExtensionFactory $extensionFactory
     */
    public function __construct(OrderItemExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $orderItem
     *
     * @return OrderItemInterface
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem)
    {
        /**
            Pay atention here: 

            $customAttrName = $orderItem->getData(self::CUSTOM_ATTR_NAME);
        */
        $customAttrName = $orderItem->getData(self::CUSTOM_ATTR_NAME);
        $extensionAttributes = $orderItem->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        /**
            Pay atention here: 

            $extensionAttributes->setCustomAttrName($customAttrName);
        */
        $extensionAttributes->setDealId($customAttrName);
        $orderItem->setExtensionAttributes($extensionAttributes);

        return $orderItem;
    }

    /**
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemSearchResultInterface $searchResult
     *
     * @return OrderItemSearchResultInterface
     */
    public function afterGetList(OrderItemRepositoryInterface $subject, OrderItemSearchResultInterface $searchResult)
    {
        $orderItems = $searchResult->getItems();

        foreach ($orderItems as &$item) {
            /**
                Pay atention here: 

                $customAttrName = $item->getData(self::CUSTOM_ATTR_NAME);
            */
            $customAttrName = $item->getData(self::CUSTOM_ATTR_NAME);
            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            /**
                Pay atention here: 

                $extensionAttributes->setCustomAttrName($customAttrName);
            */
            $extensionAttributes->setDealId($customAttrName);
            $item->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}
