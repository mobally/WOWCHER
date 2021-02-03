<?php

namespace Rvs\ExpiryProduct\Model;

class UpdateAttrProducts 
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
               $this->updateClosingDate($groupProductCollection);     
                                        
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
}

