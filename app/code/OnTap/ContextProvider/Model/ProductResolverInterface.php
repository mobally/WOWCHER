<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ContextProvider\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface ProductResolverInterface extends ResolverInterface
{
    /**
     * @param LayoutInterface $layout
     */
    public function applyToLayoutBlocks(LayoutInterface $layout): void;

    /**
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct(): ProductInterface;
}
