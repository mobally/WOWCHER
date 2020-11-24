<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\SalesAttribute\Plugin\Quote\Model;

use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use OnTap\SalesAttribute\Api\Data\OrderItemExtraInterface;
use OnTap\SalesAttribute\Model\OrderItemExtraFactory;

class OrderItemRepositoryPlugin
{
    /**
     * @var OrderItemExtensionFactory
     */
    protected OrderItemExtensionFactory $orderItemExtensionFactory;

    /**
     * @var OrderItemExtraFactory
     */
    protected OrderItemExtraFactory $orderItemExtraFactory;

    /**
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     * @param OrderItemExtraFactory $orderItemExtraFactory
     */
    public function __construct(
        OrderItemExtensionFactory $orderItemExtensionFactory,
        OrderItemExtraFactory $orderItemExtraFactory
    ) {
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
        $this->orderItemExtraFactory = $orderItemExtraFactory;
    }

    /**
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem)
    {
        return $this->_afterGet($orderItem);
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    protected function _afterGet(OrderItemInterface $orderItem)
    {
        $extensionAttributes = $orderItem->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getOrderItemExtra()) {
            return $orderItem;
        }

        /** @var OrderItemExtraInterface $orderItemExtra */
        $orderItemExtra = $this->orderItemExtraFactory->create();
        $orderItemExtra->setBusinessId(
            $orderItem->getData(OrderItemExtraInterface::BUSINESS_ID)
        );

        /** @var \Magento\Sales\Api\Data\OrderItemExtension $orderItemExtension */
        $orderItemExtension = $extensionAttributes ?: $this->orderItemExtensionFactory->create();
        $orderItemExtension->setOrderItemExtra($orderItemExtra);
        $orderItem->setExtensionAttributes($orderItemExtension);

        return $orderItem;
    }

    /**
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemSearchResultInterface $searchResult
     * @return OrderItemSearchResultInterface
     */
    public function afterGetList(OrderItemRepositoryInterface $subject, OrderItemSearchResultInterface $searchResult)
    {
        foreach ($searchResult->getItems() as &$orderItem) {
            $this->_afterGet($orderItem);
        }

        return $searchResult;
    }
}
