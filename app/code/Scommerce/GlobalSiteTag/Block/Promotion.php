<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GlobalSiteTag\Block;

use \Scommerce\GlobalSiteTag\Helper\Data;
use \Magento\Framework\View\Element\Template\Context;

/**
 * Catalog Product View Page Block
 */
class Promotion extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * Promotion constructor.
     * @param Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->_layout = $context->getLayout();
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Returns whether promotion tracking is enabled or not
     *
     * @return string
     */
    public function isPromotionTrackingEnabled()
    {
        return $this->getHelper()->isPromotionTrackingEnabled();
    }

    /**
     * Return helper object
     *
     * @return Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }
}