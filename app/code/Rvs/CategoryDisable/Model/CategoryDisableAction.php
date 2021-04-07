<?php
namespace Rvs\CategoryDisable\Model;

class CategoryDisableAction 
{
	protected $_storeManager;
	protected $_categoryCollection;
	protected $_productCollectionFactory;
protected $_categoryFactory;

	public function __construct(
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    
) {
    $this->_storeManager = $storeManager;
    $this->_categoryCollection = $categoryCollection;
    $this->_productCollectionFactory = $productCollectionFactory;
    $this->_categoryFactory = $categoryFactory;
}

public function getCategories(){
     $categories = $this->_categoryCollection->create()                              
         ->addAttributeToSelect('*')
         ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
     foreach ($categories as $category){
         $category_id = $category->getId();
         $product_count = $this->getProductCollection($category_id);
         if($product_count == 0){
         $category->setData('is_active', 0);
		 $category->setData('store_id', 2);
		 $category->save();
         }
     }
 $this->spainStoreCategories();
 $this->frenchStoreCategories();
 }
 

public function spainStoreCategories(){
     $categories = $this->_categoryCollection->create()                              
         ->addAttributeToSelect('*')
         ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
     foreach ($categories as $category){
         $category_id = $category->getId();
         $product_count = $this->getProductCollection($category_id);
         if($product_count == 0){
         $category->setData('is_active', 0);
		 $category->setData('store_id', 3);
		 $category->save();
         }
     }
 }
 
 public function frenchStoreCategories(){
     $categories = $this->_categoryCollection->create()                              
         ->addAttributeToSelect('*')
         ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
     foreach ($categories as $category){
         $category_id = $category->getId();
         $product_count = $this->getProductCollection($category_id);
         if($product_count == 0){
         $category->setData('is_active', 0);
		 $category->setData('store_id', 4);
		 $category->save();
         }
     }
 }


public function getProductCollection($category_id)
{
    $category = $this->_categoryFactory->create()->load($category_id);
    $collection = $this->_productCollectionFactory->create();
    $collection->addAttributeToSelect('*');
    $collection->addCategoryFilter($category);
    $collection->addAttributeToFilter('type_id', array('eq' => 'grouped'));
    $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
    $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
    return count($collection);
}

	public function execute()
	   {
	   
	   $this->getCategories();
	   }

}
