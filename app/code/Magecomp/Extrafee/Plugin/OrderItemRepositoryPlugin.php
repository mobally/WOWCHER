<?php

namespace Magecomp\Extrafee\Plugin;

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
     * Order feedback field name
     */
    const DUTY_PAID = 'dutypaid';
    const DUTY_FEE = 'fee';

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
        $dutypaid = $orderItem->getData(self::DUTY_PAID);
        $dutyfee = $orderItem->getData(self::DUTY_FEE);
        $extensionAttributes = $orderItem->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setDutypaid($dutypaid);
        $extensionAttributes->setFee($dutyfee);
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
            $dutypaid = $item->getData(self::DUTY_PAID);
            $dutyfee = $item->getData(self::DUTY_FEE);
            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setDutypaid($dutypaid);
            $extensionAttributes->setFee($dutyfee);
            $item->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}
