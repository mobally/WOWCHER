<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Model\CategoryResolver;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnTap\ContextProvider\Model\CategoryResolverInterface;

class DefaultCategory implements CategoryResolverInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @var CategoryInterface
     */
    protected CategoryInterface $category;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * FromCategoryViewUrl constructor.
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getCategory(): CategoryInterface
    {
        $group = $this->storeManager->getGroup();
        return $this->categoryRepository->get(
            $group->getRootCategoryId(),
            $this->storeManager->getStore()->getId()
        );
    }
}
