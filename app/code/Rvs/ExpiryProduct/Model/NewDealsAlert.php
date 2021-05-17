<?php

namespace Rvs\ExpiryProduct\Model;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;


class NewDealsAlert 
{
	protected $productCollectionFactory;
	protected $_date;
	protected $dateTime;
	private $state;
	protected $productAction;
	protected $transportBuilder;
	protected $storeManager;
	protected $inlineTranslation;


	public function __construct(
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
    \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
    \Magento\Catalog\Model\ProductFactory $productFactory,
    \Magento\Framework\App\State $state,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
    TransportBuilder $transportBuilder,
    StoreManagerInterface $storeManager,
    StateInterface $states
  ) {
    $this->productCollectionFactory = $productCollectionFactory;
    $this->_date =  $date;    
    $this->dateTime = $dateTime;
    $this->productFactory = $productFactory;
    $this->state = $state;
    $this->productRepository = $productRepository;
    $this->productAction = $productAction;
    $this->transportBuilder = $transportBuilder;
    $this->storeManager = $storeManager;
    $this->inlineTranslation = $states;
}

public function execute()
   {
	   
	   $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$current_date = $this->_date->date()->format('Y-m-d');
			//$current_date = '2021-05-14';
			$sql = "Select sku FROM catalog_product_entity WHERE DATE_FORMAT(created_at, '%Y-%m-%d') = '$current_date'";
			$result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
			$items = "";  
			  foreach($result as $val){
				   $items .= $val['sku'].'<br />';
			   }
       if($items == ''){
		$items = "No new deals added today $current_date";
	   }		   
       
       $templateId = '10'; // template id
        $fromEmail = 'info@wowcher.com';  // sender Email id
        $fromName = 'WOWCHER';             // sender Name
        $toEmail = 'thomas.routledge@wowcher.co.uk'; // receiver email id

        try {
            // template variables pass here
            $templateVars = [
                'msg' => $items,
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->addCc("mehul.parekh@wowcher.co.uk")
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
        print_r($e);
            
        }
                                        
   }
}
