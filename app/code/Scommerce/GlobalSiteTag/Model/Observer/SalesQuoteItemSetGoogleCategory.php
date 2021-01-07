<?php
/**
 * Copyright © 2017 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GlobalSiteTag\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class SalesQuoteItemSetGoogleCategory implements ObserverInterface
{
    /**
     * @var \Scommerce\GoogleUniversalAnalytics\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $_cookie;

    /**
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookie
     * @param \Scommerce\GoogleUniversalAnalytics\Helper\Data $helper
     */
    public function __construct(\Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookie,
                                \Scommerce\GlobalSiteTag\Helper\Data $helper)
    {
        $this->_cookie = $cookie;
        $this->_helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnhancedEcommerceEnabled()) {
            $quoteItem = $observer->getQuoteItem();
            $product = $observer->getProduct();
            $category = $quoteItem->getGoogleCategory();

            if ($category == '' || !isset($category)) {
                $cookie = json_decode($this->_cookie->getCookie('productListData-' . $product->getData('sku')));
                if (isset($cookie)) {
                    $category = $cookie->category;
                }

                if (!isset($category) || strlen($category) == 0) {
                    $category = $this->_helper->getProductCategoryName($product);
                }
                $quoteItem->setGoogleCategory($category);
            }
        }
    }
}