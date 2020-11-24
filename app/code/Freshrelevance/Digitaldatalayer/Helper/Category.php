<?php

namespace Freshrelevance\Digitaldatalayer\Helper;

use Exception;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\CategoryFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Category helper class
 *
 * Extract details of categories from products
 */
class Category extends AbstractHelper
{
    const IGNORED_CATEGORY = 'All Products';
    /** @var CollectionFactory */
    protected $categoryCollectionFactory;
    /** @var ResourceConnection */
    protected $resource;
    /** @var CategoryFactory */
    protected $categoryFactory;

    protected $catIdToName = null;

    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        ResourceConnection $resource,
        CategoryFactory $categoryFactory,
        Context $context
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->resource                  = $resource;
        $this->categoryFactory           = $categoryFactory;
        parent::__construct($context);
    }

    /**
     * Get category names by ID, lazy loading from a single collection
     *
     * @param int $catId
     *
     * @return string|null
     * @throws LocalizedException
     */
    public function getCatNameById($catId)
    {
        // Lazily load category names by ID
        if ($this->catIdToName === null) {
            // Load in categories and arrange into array mapping id to name
            $dirs              = $this->categoryCollectionFactory
                ->create()
                ->addAttributeToSelect('name');
            $this->catIdToName = [];
            foreach ($dirs as $dir) {
                $this->catIdToName[$dir->getId()] = $dir->getName();
            }
        }

        return isset($this->catIdToName[$catId]) ? $this->catIdToName[$catId] : null;
    }

    /**
     * Get category IDs of productIds, with primary as first index
     *
     * @param array $productIds
     * @return array productId => array(categoryIds)
     * @throws Exception
     */
    public function getCategoriesByProductId($productIds)
    {
        $select      = $this->resource->getConnection()->select();
        $entityTable = $this->categoryFactory->create()->getCategoryProductTable();
        $select->from($entityTable, ['category_id', 'position', 'product_id']);
        $select->where('product_id IN(?)', $productIds);
        //$select->order('product_id, position ASC');

        // Get categories per product from call
        $results      = $this->resource->getConnection()->fetchAll($select);
        $perProductId = [];
        foreach ($results as $row) {
            if (!isset($perProductId[$row['product_id']])) {
                $perProductId[$row['product_id']] = [];
            }
            $perProductId[$row['product_id']][] = $row['category_id'];
        }

        return $perProductId;
    }

    /**
     * Get categories from product
     *
     * @param $product
     *
     * @return array
     * @throws LocalizedException
     */
    public function getProductCategories($product)
    {
        $categories  = [];
        $categoryIds = $product->getCategoryIds();
        $i           = 0;
        foreach ($categoryIds as $categoryId) {
            if ($i == 1) {
                $categories['primaryCategory'] = $this->getCatNameById($categoryId);
            } elseif ($i == 0) {
                $categories['subCategory1'] = $this->getCatNameById($categoryId);
            } else {
                $categories['subCategory' . $i] = $this->getCatNameById($categoryId);
            }
            $i++;
        }
        $categories['productType'] = $product->getTypeId();

        return $categories;
    }

    /**
     * @param \Magento\Catalog\Model\Category $currentCategory
     * @return array
     */
    public function getCategoryTree($currentCategory)
    {
        $tree         = [];
        $initialLevel = $currentCategory->getLevel();
        $level        = $currentCategory->getLevel();
        if ($level > 2) {
            $category = $currentCategory;
            while ($level > 1) {
                $tree[$level] = $category->getName();
                $category     = $category->getParentCategory();
                $level--;
            }
        } else {
            if ($level > 1) {
                $tree[$level] = $currentCategory->getName();
            }
        }
        if ($tree['2'] == self::IGNORED_CATEGORY) {
            $newTree = [];
            for ($level = 2; $level < $initialLevel; $level++) {
                $newTree[$level] = $tree[strval($level + 1)];
            }
            return $newTree;
        }
        return $tree;
    }
}
