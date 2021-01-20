<?php

namespace Rvs\ExpiryProduct\Model;

class DisableProducts 
{
	protected $productCollectionFactory;
	protected $_date;
	protected $dateTime;
	private $state;
	protected $productAction;
	public function __construct(
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
    \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
    \Magento\Catalog\Model\ProductFactory $productFactory,
    \Magento\Framework\App\State $state,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
    \Magento\Store\Model\StoreManagerInterface $storeManager
  ) {
    $this->productCollectionFactory = $productCollectionFactory;
    $this->_date =  $date;    
    $this->dateTime = $dateTime;
    $this->productFactory = $productFactory;
    $this->state = $state;
    $this->productRepository = $productRepository;
    $this->productAction = $productAction;
    $this->_storeManager = $storeManager;
}

public function execute()
   {
   $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
       $groupProductCollection = $this->productCollectionFactory->create() 
                                        ->addAttributeToSelect('*')
                                        ->addAttributeToFilter('type_id', array('eq' => 'grouped'));
                       
       $date_time = $this->_date->date()->format('Y-m-d H:i:s');  
       $timeStamp = $this->dateTime->gmtTimestamp($date_time);
       $productIdsActivate = [];
	$productIdsDeactivate = [];               
       foreach($groupProductCollection as $val)
       {
       
          $expiry_date = $val->getClosingdate();
          if($timeStamp > $expiry_date && $expiry_date != '')
          {
          //echo $val->getId().'hello';
          $productIdsActivate[] = $val->getId();
          
          } else{
          $productIdsDeactivate[] = $val->getId();
          }
       }
       //$storeIds = array_keys($this->_storeManager->getStores());
       $storeIds = array(0,1,2,3,4);
   
$updateAttributes['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
$updateAttributes['is_expiry'] = 1;
foreach ($storeIds as $storeId) {
    $this->productAction->updateAttributes($productIdsActivate, $updateAttributes, $storeId);
}
                                        
   }
}
