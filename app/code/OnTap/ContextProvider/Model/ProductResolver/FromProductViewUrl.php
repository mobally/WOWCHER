<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Model\ProductResolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use OnTap\ContextProvider\Model\ProductResolverInterface;

class FromProductViewUrl implements ProductResolverInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var ProductInterface
     */
    protected ProductInterface $product;

    /**
     * FirstInCategory constructor.
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function applyToLayoutBlocks(LayoutInterface $layout): void
    {
        // noop
    }

    /**
     * {@inheritDoc}
     */
    public function getProduct(): ProductInterface
    {
        if (isset($this->product)) {
            return $this->product;
        }

        $id = $this->request->getParam('id');
        $this->product = $this->productRepository->getById($id);

        return $this->product;
    }
}
