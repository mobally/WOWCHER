<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\SalesAttribute\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use OnTap\SalesAttribute\Api\Data\QuoteItemExtraInterface;

class QuoteItemExtra extends AbstractExtensibleModel implements QuoteItemExtraInterface
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\OnTap\SalesAttribute\Model\ResourceModel\QuoteItemExtra::class);
        parent::_construct();
    }

    /**
     * @inheritDoc
     */
    public function getItemId(): int
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setItemId(int $id): QuoteItemExtraInterface
    {
        return $this->setData(self::ITEM_ID, $id);
    }

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
    public function setBusinessId(?string $businessId): QuoteItemExtraInterface
    {
        return $this->setData(self::BUSINESS_ID, $businessId);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \OnTap\SalesAttribute\Api\Data\QuoteItemExtraExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
