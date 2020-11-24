<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\SalesAttribute\Api\Data;

interface OrderItemExtraInterface
{
    const BUSINESS_ID = 'business_id';

    /**
     * @return string|null
     */
    public function getBusinessId(): ?string;

    /**
     * @param string|null $businessId
     * @return $this
     */
    public function setBusinessId(?string $businessId): self;
}
