<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Menu\Block;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Cms\Block\Block;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Topmenu;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use OnTap\Menu\Model\CategoryListModel;

class Menu extends Topmenu
{
    const SPACE = ' ';
    const CATEGORY_LEVEL_2 = 2;

    /**
     * @var PriceCurrencyInterface
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @var CategoryFactory
     */
    private CategoryFactory $categoryFactory;

    /**
     * @var CategoryCollectionFactory
     */
    private CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * @var CategoryListModel
     */
    private CategoryListModel $categoryListModel;

    /**
     * Menu constructor.
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param CategoryFactory $categoryFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryListModel $categoryListModel
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        PriceCurrencyInterface $priceCurrency,
        CategoryFactory $categoryFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryListModel $categoryListModel,
        array $data = []
    ) {
        parent::__construct($context, $nodeFactory, $treeFactory, $data);
        $this->priceCurrency = $priceCurrency;
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryListModel = $categoryListModel;
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        $dealTextPrefix = __('All');
        //$dealTextSuffix = __('Deals');
$dealTextSuffix = "";
        if ((!$child->hasChildren())) {
            return $html;
        }

        if ($childLevel == 0) {
            $colStops = [];
            if ($childLevel == 0 && $limit) {
                $colStops = $this->_columnBrake($child->getChildren(), $limit);
            }
            $html .= '<ul class="level' . $childLevel . ' ' . $childrenWrapClass . '">';
            $html .= "<li class='column-1'><a href='" . $child->getUrl() . "'>"
                . $dealTextPrefix . self::SPACE . $child->getName() . self::SPACE
                . $dealTextSuffix
                . "</a></li>";

            $html .= '<div class="column-2">';
            $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
            $html .= '</div>';
            $html .= $this->getCmsBlockData($child);

            $html .= '</ul>';

            return $html;
        }
        return $html;
    }

    /**
     * Get CMS block content
     *
     * @param string $child
     * @return string
     */
    public function getCmsBlockData($child): string
    {
        $cmsHtml = '';
        $cmsBlockId = $this->getCmsBlockId($child->getId());

        try {
            if ($cmsBlockId) {
                $cmsHtml = $this->getLayout()
                    ->createBlock(Block::class)
                    ->setBlockId($cmsBlockId)
                    ->toHtml();
            }
            return $cmsHtml ? '<li class="column-3 ui-menu-item">' . $cmsHtml . '</li>' : '';

        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return '';
    }

    /**
     * Get CMS block ID by Category
     *
     * @param string $childId
     * @return string
     */
    protected function getCmsBlockId($childId): string
    {
        try {
            $catId = explode('category-node-', $childId)[1];
            $categoryData = $this->categoryFactory->create()->load($catId);
            return (string) $categoryData->getData('dropdown_cms_static_block');
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return '';
    }

    /**
     * Get Categories for mobile menu
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        $model = $this->categoryListModel;

        if (empty($model->getItems())) {
            $model->setItems($this->getSecondLevelCategories());
        }

        return $model->getItems();
    }

    /**
     * Get all level 2 categories
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSecondLevelCategories()
    {
        $data = [];

        try {
            $collection = $this->categoryCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addIsActiveFilter();
            $collection->addAttributeToFilter('level', self::CATEGORY_LEVEL_2);
            $collection->addAttributeToFilter('include_in_menu', 1);
            $collection->addOrderField('position');

            foreach ($collection as $category) {
                $data[] = [
                    'url' => $category->getUrl(),
                    'label' => $category->getName()
                ];
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $data;
    }
}
