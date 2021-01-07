<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GlobalSiteTag\Block;

/**
 * Cart page
 */
class Cart extends \Magento\Framework\View\Element\Template
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

    /*
    * @var \Magento\Framework\Session\SessionManagerInterface
    */
    protected $_coreSession;

    const CROSSSELL_LIST_NAME = 'Cross-sell Products';

    /**
     * Cart constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Catalog\Model\Layer\Resolver $layer
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Session\SessionManagerInterface $coresession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
        \Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Model\Layer\Resolver $layer,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\SessionManagerInterface $coresession,
        array $data = []
    ) {
        $this->_layout = $context->getLayout();
        $this->_helper = $helper;
        $this->_coreSession = $coresession;
        $this->_layer = $layer->get();
        $this->_registry = $registry;
        $this->_category = $category;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getItems()
    {

        return $this->_layout->getBlockSingleton('Magento\Checkout\Block\Cart\Crosssell')->getItems();
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
     * @return _type
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
     * Return catalog current category object
     *
     * @return \Magento\Catalog\Model\Category
     */

    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
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
     * @return string
     */
    public function getCrosssellListName()
    {
        return self::CROSSSELL_LIST_NAME;
    }

    /**
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    public function getCoreSession()
    {
        return $this->_coreSession;
    }
}