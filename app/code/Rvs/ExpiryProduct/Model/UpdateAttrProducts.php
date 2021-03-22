<?php

namespace Rvs\ExpiryProduct\Model;
use Rvs\ExpiryProduct\Model\DataFactory;
use Psr\Log\LoggerInterface;

class UpdateAttrProducts 
{
	protected $productCollectionFactory;
	protected $_date;
	protected $dateTime;
	private $state;
	protected $productAction;
	protected $_modelHelloFactory;
	protected $logger;
	
	public function __construct(
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
    \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
    \Magento\Catalog\Model\ProductFactory $productFactory,
    \Magento\Framework\App\State $state,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    LoggerInterface $logger,
    DataFactory $modelHelloFactory
  ) {
    $this->productCollectionFactory = $productCollectionFactory;
    $this->_date =  $date;    
    $this->dateTime = $dateTime;
    $this->productFactory = $productFactory;
    $this->state = $state;
    $this->productRepository = $productRepository;
    $this->productAction = $productAction;
    $this->_storeManager = $storeManager;
    $this->_modelHelloFactory = $modelHelloFactory;
    $this->logger = $logger;
}

	public function execute()
   {
   $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
       $groupProductCollection = $this->productCollectionFactory->create() 
                                        ->addAttributeToSelect('*');
                                        //->addAttributeToFilter('type_id', array('eq' => 'grouped'));
               //$this->updateClosingDate($groupProductCollection);     
               $this->updateBusinessData($groupProductCollection);
                
                 
   }
   
   public function updateClosingDate($groupProductCollection)
   {
   	$storeIds = array(0,1,2,3,4);
       foreach($groupProductCollection as $val)
       {
         $expiry_date = substr($val->getClosingdate(), 0, -3);
         $updateAttributes['closing_dates_time'] = date('m/d/Y H:i:s', $expiry_date);
          foreach ($storeIds as $storeId) {
	    $this->productAction->updateAttributes([$val->getId()], $updateAttributes, $storeId); 
	}
       }
  }
  
  public function updateBusinessData($groupProductCollection)
   {
   	$this->getBusinessData($groupProductCollection);
   }
  
  public function getBusinessData($groupProductCollection)
   {
	   $business_id = "";
	   $storeIds = array(0,1,2,3,4);
	   $this->logger->debug(sprintf('Import source created, validating...'));
		 foreach($groupProductCollection as $val){
         $business_id = $val->getBusinessId();
         $business_id = $val->getBusinessId();
         
       //$business_id = rtrim($business_id, ',');
   	$resultPage = $this->_modelHelloFactory->create();
        $collection = $resultPage->getCollection();
        $collection->addFieldToFilter('main_table.business_id',array('eq' => $business_id));
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(['business_email']);
        $data = $collection->getData();
        foreach($data as $value){
        $updateAttributes['merchant_email'] = $value['business_email'];
        foreach ($storeIds as $storeId) {
	    $this->productAction->updateAttributes([$val->getId()], $updateAttributes, $storeId); 
	}
  }
 }
 
 }
 
	/*public function setBusinessEmail($groupProductCollection){
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productRepository=$objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface'); 
		foreach ($groupProductCollection as $groupproduct)
		{
		echo $merchant_email = $groupproduct->getMerchantEmail();
		$associatedProducts = $groupproduct->getTypeInstance()->getAssociatedProducts($groupproduct);
		foreach($associatedProducts as $product){
		$pro_id = $product->getId();
		$product = $productRepository->getById($pro_id);
		$product->setMerchantEmail($merchant_email);
		$product->getResource()->saveAttribute($product, 'merchant_email');
			}
		 }
	}*/
 
}  



