<?php

namespace Rvs\ExpiryProduct\Model;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;


class DisableDealsAlert 
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
       $groupProductCollection = $this->productCollectionFactory->create() 
                                        ->addAttributeToSelect('*')
                                        ->addAttributeToFilter('type_id', array('eq' => 'grouped'));
             $current_date = $this->_date->date()->format('d-m-Y');          
       $date_time = $this->_date->date()->format('Y-m-d H:i:s');
       $current_timeStamp = $this->dateTime->gmtTimestamp($date_time).'000';
       $items = "";
       foreach($groupProductCollection as $val)
       {
       
          $expiry_date = $val->getClosingdate();
          $is_expiry = $val->getIsExpiry();
          if($is_expiry == 1 && $expiry_date > $current_timeStamp)
          {
          $items .= $val->getSku().'<br />';
          }
          
       }
       /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.$current_date.'.log');
	$logger = new \Zend\Log\Logger();
	$logger->addWriter($writer);
	$logger->info($items);*/
       
       $templateId = '9'; // template id
        $fromEmail = 'info@wowcher.com';  // sender Email id
        $fromName = 'Admin';             // sender Name
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
            $this->_logger->info($e->getMessage());
        }
                                        
   }
}
