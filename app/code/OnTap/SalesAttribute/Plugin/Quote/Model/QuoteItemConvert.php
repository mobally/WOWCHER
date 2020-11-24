<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\SalesAttribute\Plugin\Quote\Model;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;

class QuoteItemConvert
{
    /**
     * @param ToOrderItem $subject
     * @param OrderItemInterface $orderItem
     * @param AbstractItem $item
     * @param array $additional
     * @return OrderItemInterface
     */
    public function afterConvert(
        ToOrderItem $subject,
        OrderItemInterface $orderItem,
        AbstractItem $item,
        $additional = []
    ) {
        if ($item->getExtensionAttributes() && $item->getExtensionAttributes()->getQuoteItemExtra()) {
            $quoteItemExtra = $item->getExtensionAttributes()->getQuoteItemExtra();
            $orderItem->setBusinessId(
                $quoteItemExtra->getBusinessId()
            );
        }
        return $orderItem;
    }
}
