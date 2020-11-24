<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Model\ProductResolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutInterface;
use OnTap\ContextProvider\Model\ProductResolverInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;

class FirstInCategory implements ProductResolverInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @var Registry
     */
    protected Registry $registry;

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
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     */
    public function __construct(
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry
    ) {
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    public function applyToLayoutBlocks(LayoutInterface $layout): void
    {
        $product = $this->getProduct();

        // some blocks use this registry hack
        if (!$this->registry->registry('product')) {
            $this->registry->register('product', $product);
        }

        if (!$this->registry->registry('current_product')) {
            $this->registry->register('current_product', $product);
        }

        /** @var \Magento\Catalog\Block\Product\View $productView */
        $productView = $layout->getBlock('product.info');
        if ($productView) {
            $productView->setData('product_id', $product->getId());
        }
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

        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->categoryRepository->get($id);

        $product = $category->getProductCollection()
            ->setOrder('deal_position','DESC');
//            ->addFieldToFilter('deal_position', ['notnull' => true]);

//        $product->getSelect()
//            ->order(new \Zend_Db_Expr('CAST(`deal_position` AS DECIMAL) DESC'));

        $product->getSelect()->limit(1);

        $product = $product->getFirstItem();

        if ($product->getId() === null) {
            throw new NoSuchEntityException(__('This category does not seem to have any products to display.'));
        }

        $this->product = $this->productRepository->getById($product->getId());
        return $this->product;
    }
}
