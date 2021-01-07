<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GlobalSiteTag\Block;

/**
 * Catalog Product View Page Block
 */
class View extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\Registry
     */

    protected $_registry;

    /**
     * @var \Scommerce\GlobalSiteTag\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    const RELATED_LIST_NAME = 'Related Products';

    const UPSELL_LIST_NAME = 'Up-sell Products';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_helper = $helper;
        $this->_product = $product;
        parent::__construct($context, $data);
    }

    /**
     * Return catalog product object
     *
     * @return \Magento\Catalog\Model\Product
     */

    public function getProduct()
    {
        return $this->_registry->registry('product');
    }

    /**
     * Return catalog product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProducts($_productIds)
    {
        return $this->getProduct()
            ->getCollection()
            ->addAttributeToSelect(array('name','sku','price'))
            ->addAttributeToFilter('entity_id',array('in' => $_productIds))
            ->addUrlRewrite();
    }

    /**
     * Return catalog current category object
     *
     * @return \Magento\Catalog\Model\Category
     */

    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Return helper object
     *
     * @return \Scommerce\GlobalSiteTag\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Render block html if google universal analytics conversion is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_helper->isEnabled() ? parent::_toHtml() : '';
    }

    /**
     * @return string
     */
    public function getRelatedListName()
    {
        return self::RELATED_LIST_NAME;
    }

    /**
     * @return string
     */
    public function getUpsellListName()
    {
        return self::UPSELL_LIST_NAME;
    }
}
