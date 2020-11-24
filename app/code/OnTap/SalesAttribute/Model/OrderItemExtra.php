<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\SalesAttribute\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use OnTap\SalesAttribute\Api\Data\OrderItemExtraInterface;

class OrderItemExtra extends AbstractExtensibleModel implements OrderItemExtraInterface
{
    /**
     * @inheritDoc
     */
    public function getBusinessId(): ?string
    {
        return $this->getData(self::BUSINESS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setBusinessId(?string $businessId): OrderItemExtraInterface
    {
        return $this->setData(self::BUSINESS_ID, $businessId);
    }
}
