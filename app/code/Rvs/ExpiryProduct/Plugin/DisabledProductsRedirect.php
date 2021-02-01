<?php

namespace Rvs\ExpiryProduct\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use \Magento\Catalog\Controller\Product as ProductController;

class DisabledProductsRedirect
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryInterface;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

private $cookieManager;
 
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryInterface
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryInterface,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory,
        ScopeConfigInterface $scopeConfig,
        Http $request,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->productRepository = $productRepository;
        $this->categoryInterface = $categoryInterface;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->stockItem = $stockItem;
         $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }
    /**
     * @param ProductController $subject
     */
    public function aroundExecute(ProductController $subject, callable $proceed)
    {
    
	 $gclid = $this->request->getParam('gclid');
	 $msclkid = $this->request->getParam('msclkid');
	 $ito = $this->request->getParam('ito');
	 $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);
 	if($gclid){
	$this->cookieManager->setPublicCookie('gclidnew',$gclid,$publicCookieMetadata);
	}
	if($msclkid){
	$this->cookieManager->setPublicCookie('msclkidnew',$msclkid,$publicCookieMetadata);
	}
	if($ito){
	$this->cookieManager->setPublicCookie('itonew',$ito,$publicCookieMetadata);
	}
        return $proceed();
    }
    
}
