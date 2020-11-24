<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface CategoryResolverInterface extends ResolverInterface
{
    /**
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    public function getCategory(): CategoryInterface;
}
