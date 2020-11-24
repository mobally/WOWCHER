<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Model\ProductResolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use OnTap\ContextProvider\Model\ProductResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class FirstProductGlobally implements ProductResolverInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ProductInterface
     */
    protected ProductInterface $product;

    /**
     * @var SortOrderBuilder
     */
    protected SortOrderBuilder $sortOrderBuilder;

    /**
     * FirstInCategory constructor.
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
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

        $sortOrder = $this->sortOrderBuilder
            ->setField('deal_position')
            ->setDescendingDirection()
            ->create();

        $websiteId = $this->storeManager
            ->getWebsite()
            ->getId();

        // TODO: Add more filters.. e.g. stock, status etc
        $search = $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(1)
            ->addFilter('type_id', 'grouped')
            ->addFilter('status', 1)
            ->addFilter('website_id', $websiteId)
            //->addFilter('in_stock', 1)
            ->addSortOrder($sortOrder)
            ->create();

        $list = $this->productRepository->getList($search);

        $items = $list->getItems();
        if (empty($items)) {
            throw new RuntimeException(__('There are no products display in this store. They could all be out of stock or disabled, or unsupported type.'));
        }
        $this->product = array_pop($items);

        return $this->product;
    }
}
