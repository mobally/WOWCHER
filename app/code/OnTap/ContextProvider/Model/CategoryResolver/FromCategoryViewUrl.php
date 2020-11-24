<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ContextProvider\Model\CategoryResolver;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnTap\ContextProvider\Model\CategoryResolverInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class FromCategoryViewUrl implements CategoryResolverInterface
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
     * @var CategoryInterface
     */
    protected CategoryInterface $category;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * FromCategoryViewUrl constructor.
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getCategory(): CategoryInterface
    {
        if (isset($this->category)) {
            return $this->category;
        }

        $this->category = $this->categoryRepository->get(
            $this->request->getParam('id'),
            $this->storeManager->getStore()->getId()
        );

        return $this->category;
    }
}
