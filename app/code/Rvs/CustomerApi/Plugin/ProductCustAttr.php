<?php

namespace Rvs\CustomerApi\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;

class ProductCustAttr
{
     public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $entity
    )
    {
    	$objectManager =\Magento\Framework\App\ObjectManager::getInstance();
	$helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
        $product = $entity;
         $cats = $product->getCategoryIds();
         $firstCategoryId = "";
                                if(count($cats) ){

                                    if($cats[0]==2){
                                        $firstCategoryId = $cats[1];
                                    }
                                    else{
                                          $firstCategoryId = $cats[0];
                                    }
                                    }
	$product_url = $product->setCategoryId($firstCategoryId)->getProductUrl();
                            
        /** Get Current Extension Attributes from Product */
        $extensionAttributes = $product->getExtensionAttributes();
        $imageUrl = $helperImport->init($product, 'product_page_image')
                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                ->resize(250,250)
                ->getUrl();
        $extensionAttributes->setGoogleImage($imageUrl); // custom field value set
        $extensionAttributes->setGoogleCategoryProductUrl($product_url);
        $product->setExtensionAttributes($extensionAttributes);
        return $product;
    }

    public function afterGetList(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterface $searchCriteria
    ) : \Magento\Catalog\Api\Data\ProductSearchResultsInterface
    {
    $objectManager =\Magento\Framework\App\ObjectManager::getInstance();
	$helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
        
        $products = [];
        foreach ($searchCriteria->getItems() as $entity) {
        $product = $entity;
        $cats = $product->getCategoryIds();
         $firstCategoryId = "";
                                if(count($cats) ){

                                    if($cats[0]==2){
                                        $firstCategoryId = $cats[1];
                                    }
                                    else{
                                          $firstCategoryId = $cats[0];
                                    }
                                    }
	$product_url = $product->setCategoryId($firstCategoryId)->getProductUrl();
            /** Get Current Extension Attributes from Product */
            $extensionAttributes = $entity->getExtensionAttributes();
        $imageUrl = $helperImport->init($entity, 'product_page_image')
                ->setImageFile($entity->getSmallImage()) // image,small_image,thumbnail
                ->resize(250,250)
                ->getUrl();
        $extensionAttributes->setGoogleImage($imageUrl); // custom field value set
        $extensionAttributes->setGoogleCategoryProductUrl($product_url);
            $entity->setExtensionAttributes($extensionAttributes);
            $products[] = $entity;
        }
        $searchCriteria->setItems($products);
        return $searchCriteria;
    }
}
