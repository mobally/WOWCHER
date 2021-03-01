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
	
	public function __construct(
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
    \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
    \Magento\Catalog\Model\ProductFactory $productFactory,
    \Magento\Framework\App\State $state,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\App\Request\Http $request
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
       
       public function getCurrentpath()
       {
       return $moduleName = $this->request->getControllerName();
       }
       
       public function getCurrentUrl()
       {
       return $parmeters = $this->request->getParams();
       }
       

}
