<?php

namespace Rvs\ExpiryProduct\Model;

class Grouplist
{
	protected $productCollectionFactory;
	protected $_date;
	protected $dateTime;
	private $state;
	protected $productAction;
	protected $request;
	protected $_registry;
	
	public function __construct(
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
    \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
    \Magento\Catalog\Model\ProductFactory $productFactory,
    \Magento\Framework\App\State $state,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Framework\Registry $registry,
    \Magento\Catalog\Model\CategoryFactory $CategoryFactory
  ) {
    $this->productCollectionFactory = $productCollectionFactory;
    $this->_date =  $date;    
    $this->dateTime = $dateTime;
    $this->productFactory = $productFactory;
    $this->state = $state;
    $this->productRepository = $productRepository;
    $this->productAction = $productAction;
    $this->_storeManager = $storeManager;
    $this->request = $request;
    $this->_registry = $registry;
    $this->categoryFactory = $CategoryFactory;
}

	public function getBottomtList()
   	{
       $groupProductCollection = $this->productCollectionFactory->create() 
                                        ->addAttributeToSelect('*')
                                        ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
                                        ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                                        ->setPage(4,6);
        return $groupProductCollection;  
       }


	public function getLeftList()
   	{
   
       $groupProductCollection = $this->productCollectionFactory->create() 
                                        ->addAttributeToSelect('*')
                                        ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
                                        ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                                        ->setPage(5,8);
                                        
       return $groupProductCollection;  
       }
       
       public function getCurrentcat()
       {
       
        $category = $this->_registry->registry('current_category');
        if($category){
       return $category->getId();
       }
       }
              
       public function getProductId()
       {
       $current_product = $this->_registry->registry('current_product');
       if($current_product){
       return array($current_product->getEntityId());
       }else{
       return array(0);
       }
       }
       
       
       public function getProductCollectionFromFourRow() {
         $categoryId = $this->getCurrentcat();
         $not_in_array = $this->getProductCollectionFromThreeRow();
	 $category = $this->categoryFactory->create()->load($categoryId);
	 $collection = $category->getProductCollection()->addAttributeToSelect('*')
	 ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
         ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)->setPageSize(10)
         ->addAttributeToFilter('entity_id', array('nin' => $not_in_array));
         $result = array();
         foreach($collection as $val){
         //return $collection;
         $result[] = $val->getEntityId();
         }
         return $result;
 	}
 	
 	
 	public function getProductCollectionFromThreeRow() {
         $categoryId = $this->getCurrentcat();
	 $category = $this->categoryFactory->create()->load($categoryId);
	 $collection = $category->getProductCollection()->addAttributeToSelect('*')
	 ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
         ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)->setPageSize(3);
         $result = array();
         foreach($collection as $val){
         $result[] = $val->getEntityId();
         }
         return $result;
 	}
 	
       
       public function getProductCollectionFromCategoryRight() {
         $categoryId = $this->getCurrentcat();
	 $category = $this->categoryFactory->create()->load($categoryId);
	 $not_in_array_left = $this->getProductCollectionFromFourRow();
	 $not_in_array_parent = $this->getProductId();
	 $not_in_array = array_merge($not_in_array_left,$not_in_array_parent);
	 $collection = $category->getProductCollection()->addAttributeToSelect('*')
	 ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
         ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)->setPageSize(3)
         ->addAttributeToFilter('entity_id', array('nin' => $not_in_array));
         return $collection;
 	}
       
       
       /* Category left page collection */
       public function getProductCollectionFromCategoryLeft() {
         $categoryId = $this->getCurrentcat();
         $not_in_array_left = $this->getProductCollectionFromThreeRow();
         $not_in_array_parent = $this->getProductId();
         $not_in_array = array_merge($not_in_array_left, $not_in_array_parent);
	 $category = $this->categoryFactory->create()->load($categoryId);
	 $collection = $category->getProductCollection()->addAttributeToSelect('*')
	 ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
         ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)->setPageSize(10)
         ->addAttributeToFilter('entity_id', array('nin' => $not_in_array));
         return $collection;
 	}
 	
 	/* Category bottom page collection */
 	public function getProductCollectionFromCategoryBottom() {
         $categoryId = $this->getCurrentcat();
         $not_in_array_left = $this->getProductCollectionFromFourRow();
         $not_in_array_right = $this->getProductCollectionFromThreeRow();
         $not_in_array_parent = $this->getProductId();
         $not_in_array = array_merge($not_in_array_left, $not_in_array_right,$not_in_array_parent);
         $category = $this->categoryFactory->create()->load($categoryId);
	 $collection = $category->getProductCollection()->addAttributeToSelect('*')
	 ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
         ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)->setPageSize(20)
         ->addAttributeToFilter('entity_id', array('nin' => $not_in_array));
         return $collection;
         
 	}
       
       
       public function getCurrentpath()
       {
       return $moduleName = $this->request->getControllerName();
       }
       
       public function getCurrentUrl()
       {
       return $parmeters = $this->request->getParams();
       }
       

}
