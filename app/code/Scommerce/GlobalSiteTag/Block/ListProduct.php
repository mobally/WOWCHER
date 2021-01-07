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
class ListProduct extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Scommerce\GlobalSiteTag\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_layer;
	
	/**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * set mode
     *
     * @var string
     */
    protected $_type = 'category';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
	 * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Catalog\Model\Layer $layer
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
		\Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Model\Layer\Resolver $layer,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_layout = $context->getLayout();
        $this->_helper = $helper;
        $this->_layer = $layer->get();
		$this->_category = $category;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getProductCollection()
    {
		if ($this->getMode()=="category")
			return $this->_layout->getBlockSingleton('Magento\Catalog\Block\Product\ListProduct')->getLoadedProductCollection();
		else
			return $this->_layout->getBlockSingleton('Magento\CatalogSearch\Block\SearchResult\ListProduct')->getLoadedProductCollection();
    }

    /**
     * Return catalog view layer model
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->_layer;
    }

    /**
     * Set mode
     *
     * @param string $type
     * @return void
     */
    public function setMode($type)
    {
        $this->_type = $type;
    }

    /**
     * return mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_type;
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
     * Render block html if google tag manager pro is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_helper->isEnabled() ? parent::_toHtml() : '';
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
}