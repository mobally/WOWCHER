<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GlobalSiteTag\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class CheckoutCartAddAfter implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_cookie;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param \Magento\Framework\Session\SessionManagerInterface $coresession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Framework\Session\SessionManagerInterface $coresession,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface $logger,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookie
    ){
        $this->_productloader = $_productloader;
        $this->_coreSession = $coresession;
        $this->_request = $request;
        $this->_helper = $helper;
        $this->logger = $logger;
        $this->_cookie = $cookie;
    }

    /**
     * TODO check super_attribute (maybe in observer)
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $productId = $this->_request->getParam('product', 0);

        $qty = $this->_request->getParam('qty', 1);

        $product = $this->_productloader->create()->load($productId);

        if (!$product->getId()) {
            return;
        }

        $item = $observer->getEvent()->getData('quote_item');

        $this->logger->debug(print_r($item->getProduct()->getSku(), true));

        $productIds[] = $item->getProduct()->getId();

        if ($this->_request->getParam('related_product')) {
            $relatedProducts = explode(',', $this->_request->getParam('related_product'));
            $productIds = array_merge($productIds, $relatedProducts);
        }

        if ($product->getSku() && $cookie = $this->_cookie->getCookie('productListData-' . $product->getData('sku'))) {
            $cookie = json_decode($cookie);
        }

        if (isset($cookie->category)) {
            $category = $cookie->category;
        }

        if (!isset($category)) {
            $category = $this->_helper->getProductCategoryName($product);
        }

        $productToBasket = [];
        // add variants to main product if needed
        foreach ($productIds as $pId) {
            $product = $this->_productloader->create()->load($pId);

            $productToBasket[] = [
                'id' => $product->getSku(),
                'name' => $product->getName(),
                'category' => $category,
                'variant' => $this->_helper->getProductVariant($product, $item),
                'brand' => $this->_helper->getBrand($product),
                'price' => $product->getFinalPrice(),
                'qty' => $qty,
                'currency' => $this->_helper->getCurrencyCode()
            ];
        }
        $this->_coreSession->setProductToBasket(json_encode($productToBasket));
    }

}