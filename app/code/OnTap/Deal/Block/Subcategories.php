<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\Deal\Block;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\LocalizedException;
use OnTap\ContextProvider\Block\CategoryAwareTemplate;

class Subcategories extends CategoryAwareTemplate
{
    /**
     * @return CategoryInterface
     * @throws LocalizedException
     */
    public function getCurrentCategory(): CategoryInterface
    {
        return $this->getCategory();
    }

    /**
     * @return array
     */
    public function getCategoriesList(): array
    {
        try {
            $category = $this->getCategory();
        } catch (LocalizedException $e) {
            return [];
        }

        $categoryList = [];
        $level = (int) $category->getLevel();

        if ($level > 2) {
            // We're in 3rd or deeper level
            $categoryList = $this->getCategoryNeighbours($category);
        } else if ($level === 2) {
            // We're in second level
            $categoryList = $this->getCategoryChildren($category);
        }

        return $categoryList;
    }

    /**
     * @param CategoryInterface $category
     * @return array
     */
    protected function getCategoryNeighbours(CategoryInterface $category): array
    {
        return $this->getCategoryChildren(
            $category->getParentCategory()
        );
    }

    /**
     * @param CategoryInterface $category
     * @return array
     */
    protected function getCategoryChildren(CategoryInterface $category): array
    {
        return $category->getChildrenCategories()->getItems();
    }
}
