<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\View\Element\Template;

class ProductAwareTemplate extends Template
{
    /**
     * @return ProductInterface
     * @throws LocalizedException
     */
    public function getProduct(): ProductInterface
    {
        $resolverBlock = $this->getLayout()->getBlock('context-aware.product.resolver');
        if ($resolverBlock === false) {
            throw new RuntimeException(__('The block "context-aware.product.resolver" was not found in the layout'));
        }
        return $resolverBlock
            ->getResolver()
            ->getProduct();
    }
}
