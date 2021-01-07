<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GlobalSiteTag\Block\Checkout;

/**
 * Checkout One Page Block
 */
class Onepage extends \Magento\Framework\View\Element\Template
{

    /** @var \Magento\Checkout\Model\Session */
    private $_checkoutSession;

    /** @var \Scommerce\GlobalSiteTag\Helper\Data */
    private $_helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCartData()
    {
        $items = [];
        foreach ($this->getCartItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $product = $item->getProduct();
            $items['items'] = [
                'name'     => $item->getName(), // [string]
                'id'       => $item->getSku(), // [string]
                'price'    => $item->getBasePrice(), // [currency]
                'brand'    => $this->_helper->getBrand($product), // [string]
                'category' => $this->_helper->getQuoteGoogleCategoryName($item), // [string]
                'variant'  => $this->_helper->getProductVariant($product, $item), // [string]
                'quantity' => $item->getQty() // [int]
            ];
            if ($this->_helper->isDynamicRemarketingEnabled()) {
                if ($this->_helper->isOtherSiteEnabled()) {
                    $items['dynx_itemid'][] = $item->getSku();
                } else {
                    $items['ecomm_prodid'][] = $item->getSku();
                }
            }
        }

        if ($this->_helper->isDynamicRemarketingEnabled()) {
            if ($this->_helper->isOtherSiteEnabled()) {
                $items['dynx_pagetype'] = 'cart';
            } else {
                $items['ecomm_pagetype'] = 'cart';
            }
        }

        return $items;
    }

    /**
     * Render block html if google tag manager is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_helper->isEnabled() ? parent::_toHtml() : '';
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */

    private function getCartItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Return quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}
