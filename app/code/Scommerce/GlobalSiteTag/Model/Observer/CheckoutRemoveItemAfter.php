<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GlobalSiteTag\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class CheckoutRemoveItemAfter implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Scommerce\GlobalSiteTag\Helper\Data
     */
    protected $_helper;

	/**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
	protected $_coreSession;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Framework\Session\SessionManagerInterface $coresession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager,
                                \Magento\Framework\Session\SessionManagerInterface $coresession,
                                \Magento\Framework\App\Request\Http $request,
                                \Scommerce\GlobalSiteTag\Helper\Data $helper)
    {
        $this->_objectManager = $objectmanager;
        $this->_coreSession = $coresession;
        $this->_request = $request;
        $this->_helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->_helper->isEnabled()){

            $quoteItem = $observer->getQuoteItem();
            $product = $quoteItem->getProduct();

            $productOutBasket = array(
                'id' => $product->getSku(),
                'name' => $product->getName(),
                'category' => $this->_helper->getQuoteGoogleCategoryName($quoteItem),
                'variant' => $this->_helper->getProductVariant($product, $observer->getQuoteItem()),
                'brand' => $this->_helper->getBrand($product),
                'price' => $product->getFinalPrice(),
                'qty'=> 0,
                'currency' => $this->_helper->getCurrencyCode()
            );
			
			$this->_coreSession->setProductOutBasket(json_encode($productOutBasket));
        }
    }

}