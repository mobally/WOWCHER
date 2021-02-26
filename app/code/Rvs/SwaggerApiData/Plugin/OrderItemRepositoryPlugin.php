<?php

namespace Rvs\SwaggerApiData\Plugin;

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
    const COUNTRY_OF_MANUFACTURE = 'country_of_manufacture';
    const WARE_HOUSE_DEAL = 'ware_house_deal';
    const DUTY_HSCODE = 'duty_hscode';

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
        $country_of_manufacture = $orderItem->getData(self::COUNTRY_OF_MANUFACTURE);
        $ware_house_deal = $orderItem->getData(self::WARE_HOUSE_DEAL);
        $duty_hscode = $orderItem->getData(self::DUTY_HSCODE);
        $extensionAttributes = $orderItem->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        /**
            Pay atention here: 

            $extensionAttributes->setCustomAttrName($customAttrName);
        */
        $extensionAttributes->setCountryOfManufacture($country_of_manufacture);
        $extensionAttributes->setWareHouseDeal($ware_house_deal);
        $extensionAttributes->setDutyHscode($duty_hscode);
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
            $country_of_manufacture = $item->getData(self::COUNTRY_OF_MANUFACTURE);
            $ware_house_deal = $item->getData(self::WARE_HOUSE_DEAL);
            $duty_hscode = $item->getData(self::DUTY_HSCODE);
            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            /**
                Pay atention here: 

                $extensionAttributes->setCustomAttrName($customAttrName);
            */
            $extensionAttributes->setCountryOfManufacture($country_of_manufacture);
            $extensionAttributes->setWareHouseDeal($ware_house_deal);
            $extensionAttributes->setDutyHscode($duty_hscode);
            $item->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}
