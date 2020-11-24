<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\SalesAttribute\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface QuoteItemExtraInterface extends ExtensibleDataInterface
{
    const ITEM_ID = 'quote_item_id';
    const BUSINESS_ID = 'business_id';

    /**
     * Get quote item id
     *
     * @return int
     */
    public function getItemId(): int;

    /**
     * Set quote item id
     *
     * @param int $id
     * @return $this
     */
    public function setItemId(int $id): self;

    /**
     * @return string|null
     */
    public function getBusinessId(): ?string;

    /**
     * @param string|null $businessId
     * @return $this
     */
    public function setBusinessId(?string $businessId): self;

    /**
     * @return \OnTap\SalesAttribute\Api\Data\QuoteItemExtraExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * @param \OnTap\SalesAttribute\Api\Data\QuoteItemExtraExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \OnTap\SalesAttribute\Api\Data\QuoteItemExtraExtensionInterface $extensionAttributes
    );
}
