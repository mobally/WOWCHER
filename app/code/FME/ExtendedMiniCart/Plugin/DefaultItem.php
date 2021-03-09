<?php
/**
* FME Extensions
*
* NOTICE OF LICENSE 
*
* This source file is subject to the fmeextensions.com license that is
* available through the world-wide-web at this URL:
* https://www.fmeextensions.com/LICENSE.txt
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this extension to newer
* version in the future.
*
* @category FME
* @package FME_ExtendedMiniCart
* @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
* @license https://fmeextensions.com/LICENSE.txt
*/
namespace FME\ExtendedMiniCart\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use \Magento\Catalog\Helper\Image;
/**
 * Class DefaultItem
 *
 */
class DefaultItem
{
    protected $productRepo;
    protected $imageHelper;
  /**
    * DefaultItem constructor.
    * @param ProductRepositoryInterface $productRepository
    * @param \Magento\Checkout\Model\Session $checkoutSession
    * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
    * @param \Magento\Catalog\Helper\Image $imageHelper
    */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \FME\ExtendedMiniCart\Helper\Data $helperdata,
        \Magento\Catalog\Helper\Image $imageHelper
    ){
        $this->productRepo = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->categoryFactory = $categoryFactory;
        $this->checkoutSession = $checkoutSession;
        $this->helperdata = $helperdata;
    }
   /**
     * @param $subject
     * @param \Closure $proceed
     * @return Item $item
     */
    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        /** @var Product $product */
        $product = $this->productRepo->getById($item->getProduct()->getId());
        $relatedProducts = $product->getRelatedProducts();
        $upsellsProducts = $product->getUpSellProducts();
        $crosssellsProducts = $product->getCrossSellProducts();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');  
        $currentStore = $storeManager->getStore(); 
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        //$result=[];
        $quoteallitems=$this->checkoutSession->getQuote()->getAllItems();
        if (!empty($quoteallitems)) {
            foreach ($quoteallitems as $quoteitems) {
                $quoteProduct=$this->productRepo->getById($quoteitems->getProductId());
                $relatedProducts=$quoteProduct->getRelatedProducts();
                foreach ($relatedProducts as $relatedProduct) {
                    if ($relatedProduct->getId() == $item->getProduct()->getId()) {
                        $quoteitems->setData('relateditemsdata', null);
                    }
                }
                $upSellsProducts=$quoteProduct->getUpSellProducts();
                foreach ($upSellsProducts as $upSellsProduct) {
                    if ($upSellsProduct->getId() == $item->getProduct()->getId()) {
                        $quoteitems->setData('relateditemsdata', null);
                    }
                }
                $crossSellsProducts=$quoteProduct->getCrossSellProducts();
                foreach ($crossSellsProducts as $crossSellsProduct) {
                    if ($crossSellsProduct->getId() == $item->getProduct()->getId()) {
                        $quoteitems->setData('relateditemsdata', null);
                    }
                }
            }
        }
        $data = $proceed($item);
        /*$this->checkoutSession->setshowReleated($this->helperdata->showReleated());
        $this->checkoutSession->setshowCustomSummary($this->helperdata->showSummary());
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('FME\ExtendedMiniCart\Helper\Data');
        $data['showReleated'] = $this->helperdata->showReleated();
        $data['showCustomSummary'] = $this->helperdata->showSummary();*/
        $result['showcustomreleated'] = $this->helperdata->showReleated();
        $result['showcustomsummary'] = $this->helperdata->showSummary();
        if (!empty($relatedProducts)) {
            foreach ($relatedProducts as $related) { 
                $related = $this->productRepo->getById($related->getId());
                $imageHelper = $this->imageHelper->init($related->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                $relatedids[] = $related->getId();
                $result['relateditemsdata'] = array(
                    'relatedid'        => $related->getId(),
                    'related_sku'      => $related->getSku(),
                    'relatedpermalink' => $related->getProductUrl(),
                    'relatedtitle'     => $related->getName(),
                    'relatedraw_price' => $priceHelper->currency($related->getFinalPrice(), true, false),
                    'relatedproduct_image' =>$related->getProductForThumbnail(), 
                    'related_image' =>$currentStore->getUrl().'pub/media/catalog/product'.$related->getImage() 
                );
            }
            if (!empty($result)) {
                if (!in_array($item->getProduct()->getId(), $relatedids)) {
                  return array_merge($data,$result);  
                } else {
                    foreach ($upsellsProducts as $related) { 
                        $related = $this->productRepo->getById($related->getId());
                        $imageHelper = $this->imageHelper->init($related->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                        $upsellsids[] = $related->getId();
                        $result['relateditemsdata'] = array(
                            'relatedid'        => $related->getId(),
                            'related_sku'      => $related->getSku(),
                            'relatedpermalink' => $related->getProductUrl(),
                            'relatedtitle'     => $related->getName(),
                            'relatedraw_price' => $priceHelper->currency($related->getFinalPrice(), true, false),
                            'relatedproduct_image' =>$related->getProductForThumbnail(), 
                            'related_image' =>$currentStore->getUrl().'pub/media/catalog/product'.$related->getImage() 
                        );
                    }
                    if (!empty($result)) {
                        if (!in_array($item->getProduct()->getId(), $upsellsids)) {
                          return array_merge($data,$result);  
                        } else {
                            foreach ($crosssellsProducts as $related) { 
                                $related = $this->productRepo->getById($related->getId());
                                $imageHelper = $this->imageHelper->init($related->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                                $crosssellsids[] = $related->getId();
                                $result['relateditemsdata'] = array(
                                    'relatedid'        => $related->getId(),
                                    'related_sku'      => $related->getSku(),
                                    'relatedpermalink' => $related->getProductUrl(),
                                    'relatedtitle'     => $related->getName(),
                                    'relatedraw_price' => $priceHelper->currency($related->getFinalPrice(), true, false),
                                    'relatedproduct_image' =>$related->getProductForThumbnail(), 
                                    'related_image' =>$currentStore->getUrl().'pub/media/catalog/product'.$related->getImage() 
                                );
                            }
                            if (!empty($result)) {
                                if (!in_array($item->getProduct()->getId(), $crosssellsids)) {
                                  return array_merge($data,$result);  
                                }else {
                                    $categoryIds = $product->getCategoryIds();
                                    $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
                                    $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                                    $category = $categoryFactory->create()->load($categoryIds[0]);
                                    $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
                                    foreach ($categoryProducts as $cProducts) { 
                                        $cProduct = $this->productRepo->getById($cProducts->getId());
                                        $imageHelper = $this->imageHelper->init($cProduct->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                                        //$categoryproductids[] = $related->getId();
                                        $result['relateditemsdata'] = array(
                                            'relatedid'        => $cProduct->getId(),
                                            'related_sku'      => $cProduct->getSku(),
                                            'relatedpermalink' => $cProduct->getProductUrl(),
                                            'relatedtitle'     => $cProduct->getName(),
                                            'relatedraw_price' => $priceHelper->currency($cProduct->getFinalPrice(), true, false),
                                            'relatedproduct_image' => $cProduct->getProductForThumbnail(), 
                                            'related_image' => $currentStore->getUrl().'pub/media/catalog/product'.$cProduct->getImage() 
                                        );
                                    }
                                    return array_merge($data,$result);
                                }
                            }
                        }
                    }
                }
                
            }
        } else if (!empty($upsellsProducts)) {
            foreach ($upsellsProducts as $related) { 
                $related = $this->productRepo->getById($related->getId());
                $imageHelper = $this->imageHelper->init($related->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                $upsellsids[] = $related->getId();
                $result['relateditemsdata'] = array(
                    'relatedid'        => $related->getId(),
                    'related_sku'      => $related->getSku(),
                    'relatedpermalink' => $related->getProductUrl(),
                    'relatedtitle'     => $related->getName(),
                    'relatedraw_price' => $priceHelper->currency($related->getFinalPrice(), true, false),
                    'relatedproduct_image' =>$related->getProductForThumbnail(), 
                    'related_image' =>$currentStore->getUrl().'pub/media/catalog/product'.$related->getImage() 
                );
            }
            if (!empty($result)) {
                if (!in_array($item->getProduct()->getId(), $upsellsids)) {
                  return array_merge($data,$result);  
                } else {
                    foreach ($crosssellsProducts as $related) { 
                        $related = $this->productRepo->getById($related->getId());
                        $imageHelper = $this->imageHelper->init($related->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                        $crosssellsids[] = $related->getId();
                        $result['relateditemsdata'] = array(
                            'relatedid'        => $related->getId(),
                            'related_sku'      => $related->getSku(),
                            'relatedpermalink' => $related->getProductUrl(),
                            'relatedtitle'     => $related->getName(),
                            'relatedraw_price' => $priceHelper->currency($related->getFinalPrice(), true, false),
                            'relatedproduct_image' =>$related->getProductForThumbnail(), 
                            'related_image' =>$currentStore->getUrl().'pub/media/catalog/product'.$related->getImage() 
                        );
                    }
                    if (!empty($result)) {
                        if (!in_array($item->getProduct()->getId(), $crosssellsids)) {
                          return array_merge($data,$result);  
                        }else {
                            $categoryIds = $product->getCategoryIds();
                            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
                            $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                            $category = $categoryFactory->create()->load($categoryIds[0]);
                            $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
                            foreach ($categoryProducts as $cProducts) { 
                                $cProduct = $this->productRepo->getById($cProducts->getId());
                                $imageHelper = $this->imageHelper->init($cProduct->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                                //$categoryproductids[] = $related->getId();
                                $result['relateditemsdata'] = array(
                                    'relatedid'        => $cProduct->getId(),
                                    'related_sku'      => $cProduct->getSku(),
                                    'relatedpermalink' => $cProduct->getProductUrl(),
                                    'relatedtitle'     => $cProduct->getName(),
                                    'relatedraw_price' => $priceHelper->currency($cProduct->getFinalPrice(), true, false),
                                    'relatedproduct_image' => $cProduct->getProductForThumbnail(), 
                                    'related_image' => $currentStore->getUrl().'pub/media/catalog/product'.$cProduct->getImage() 
                                );
                            }
                            return array_merge($data,$result);
                        }
                    }
                }
            }
        } else if (!empty($crosssellsProducts)) {
            foreach ($crosssellsProducts as $related) { 
                $related = $this->productRepo->getById($related->getId());
                $imageHelper = $this->imageHelper->init($related->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                $crosssellsids[] = $related->getId();
                $result['relateditemsdata'] = array(
                    'relatedid'        => $related->getId(),
                    'related_sku'      => $related->getSku(),
                    'relatedpermalink' => $related->getProductUrl(),
                    'relatedtitle'     => $related->getName(),
                    'relatedraw_price' => $priceHelper->currency($related->getFinalPrice(), true, false),
                    'relatedproduct_image' =>$related->getProductForThumbnail(), 
                    'related_image' =>$currentStore->getUrl().'pub/media/catalog/product'.$related->getImage() 
                );
            }
            if (!empty($result)) {
                if (!in_array($item->getProduct()->getId(), $crosssellsids)) {
                  return array_merge($data,$result);  
                }else {
                    $categoryIds = $product->getCategoryIds();
                    $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
                    $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
                    $category = $categoryFactory->create()->load($categoryIds[0]);
                    $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
                    foreach ($categoryProducts as $cProducts) { 
                        $cProduct = $this->productRepo->getById($cProducts->getId());
                        $imageHelper = $this->imageHelper->init($cProduct->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                        //$categoryproductids[] = $related->getId();
                        $result['relateditemsdata'] = array(
                            'relatedid'        => $cProduct->getId(),
                            'related_sku'      => $cProduct->getSku(),
                            'relatedpermalink' => $cProduct->getProductUrl(),
                            'relatedtitle'     => $cProduct->getName(),
                            'relatedraw_price' => $priceHelper->currency($cProduct->getFinalPrice(), true, false),
                            'relatedproduct_image' => $cProduct->getProductForThumbnail(), 
                            'related_image' => $currentStore->getUrl().'pub/media/catalog/product'.$cProduct->getImage() 
                        );
                    }
                    return array_merge($data,$result);
                }
            }
        } else {
            $categoryIds = $product->getCategoryIds();
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
            $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            if(!isset($categoryIds[0])){ return array_merge($data,$result); }
            $category = $categoryFactory->create()->load($categoryIds[0]);
            $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
            foreach ($categoryProducts as $cProducts) { 
                $cProduct = $this->productRepo->getById($cProducts->getId());
                $imageHelper = $this->imageHelper->init($cProduct->getProductForThumbnail(), 'mini_cart_product_thumbnail');
                //$categoryproductids[] = $related->getId();
                $result['relateditemsdata'] = array(
                    'relatedid'        => $cProduct->getId(),
                    'related_sku'      => $cProduct->getSku(),
                    'relatedpermalink' => $cProduct->getProductUrl(),
                    'relatedtitle'     => $cProduct->getName(),
                    'relatedraw_price' => $priceHelper->currency($cProduct->getFinalPrice(), true, false),
                    'relatedproduct_image' => $cProduct->getProductForThumbnail(), 
                    'related_image' => $currentStore->getUrl().'pub/media/catalog/product'.$cProduct->getImage() 
                );
            }
            return array_merge($data,$result);
        }
    }
}
