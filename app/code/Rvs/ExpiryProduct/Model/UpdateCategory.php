<?php

namespace Rvs\ExpiryProduct\Model;
use Rvs\ExpiryProduct\Model\DataFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\CategoryProductLinkInterface;

class UpdateCategory 
{
	protected $productCollectionFactory;
	protected $_date;
	protected $dateTime;
	private $state;
	protected $productAction;
	protected $_modelHelloFactory;
	protected $logger;
	private $categoryLinkManagement;
	
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
    DataFactory $modelHelloFactory,
    CategoryLinkManagementInterface $categoryLinkManagement
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
    $this->categoryLinkManagement = $categoryLinkManagement;
}

	public function execute()
   {
   $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
   $groupProductCollection = $this->productCollectionFactory->create() 
                                        ->addAttributeToSelect('*')
                                        ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
                                        ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
   $this->updateCategory($groupProductCollection);
                 
   }
   
   public function updateCategory($groupProductCollection)
   {
  
       foreach($groupProductCollection as $val)
       {
       echo $val->getSku();
         $categoryIds = $val->getCategoryIds();
         if($categoryIds){
         $child_pro = $val->getTypeInstance()->getAssociatedProducts($val);
         foreach ($child_pro as $child) {
            $productSku = $child->getSku();
            $this->assignedProductToCategory($productSku, $categoryIds);
        }
        }
       }
  }
  
  public function assignedProductToCategory(string $productSku, array $categoryIds)
    {
        $hasProductAssignedSuccess = false;
        try {
            $hasProductAssignedSuccess = $this->categoryLinkManagement->assignProductToCategories($productSku, $categoryIds);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
 
        return $hasProductAssignedSuccess;
    }
  
  
}  



