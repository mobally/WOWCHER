<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Block;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\View\Element\Template;

class CategoryAwareTemplate extends Template
{
    /**
     * @return CategoryInterface
     * @throws LocalizedException
     */
    protected function getCategory(): CategoryInterface
    {
        $resolverBlock = $this->getLayout()->getBlock('context-aware.category.resolver');
        if ($resolverBlock === false) {
            throw new RuntimeException(__('The block "context-aware.category.resolver" was not found in the layout'));
        }
        return $resolverBlock
            ->getResolver()
            ->getCategory();
    }
}
