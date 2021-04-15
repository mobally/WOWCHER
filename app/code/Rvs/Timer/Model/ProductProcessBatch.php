<?php
namespace Rvs\Timer\Model;

use Psr\Log\LoggerInterface;
class ProductProcessBatch
{
    protected $productCollectionFactory;
    protected $_date;
    protected $dateTime;
    private $state;
    protected $productAction;
    protected $logger;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date, \Magento\Framework\Stdlib\DateTime\DateTime $dateTime, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Framework\App\State $state, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Magento\Catalog\Model\ResourceModel\Product\Action $productAction, \Magento\Store\Model\StoreManagerInterface $storeManager, LoggerInterface $logger
)
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->productFactory = $productFactory;
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->productAction = $productAction;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute()
    {

        $this
            ->state
            ->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $groupProductCollection = $this
            ->productCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', array(
            'eq' => 'grouped'
        ));
        $this->updateTimer($groupProductCollection);
        //echo count($groupProductCollection);
        
    }

    public function updateTimer($groupProductCollection)
    {
        $storeIds = array(
            0,
            1,
            2,
            3,
            4
        );
        $date = strtotime(date('Y-m-d H:i:s')) . '000';
        foreach ($groupProductCollection as $val)
        {

            $countdown_timer = $val->getCountdownTimer();
            if ($countdown_timer < $date && $countdown_timer != '')
            {
                echo $val->getSku();
                $time_new = mt_rand(0, 23) . ":" . str_pad(mt_rand(0, 59) , 2, "0", STR_PAD_LEFT) . ':59 ';
                $NewDate = Date('Y-m-d ' . $time_new, strtotime('+2 days'));
                $countdown_timer = strtotime($NewDate) . '000';
                $updateAttributes['countdown_timer'] = $countdown_timer;
                foreach ($storeIds as $storeId)
                {
                    $this
                        ->productAction
                        ->updateAttributes([$val->getId() ], $updateAttributes, $storeId);
                }
            }
        }
    }

}


