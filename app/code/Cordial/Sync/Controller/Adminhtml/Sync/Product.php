<?php
/**
 * Cordial/Magento Integration RFP
 *
 * @category    Cordial
 * @package     Cordial_Sync
 * @author      Cordial Team <info@cordial.com>
 * @copyright   Cordial (http://cordial.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cordial\Sync\Controller\Adminhtml\Sync;

class Product extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /*
     * @var \Cordial\Sync\Model\Sync
     */
    protected $sync = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Cordial\Sync\Helper\Config
     */
    protected $helperConfig;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Cordial\Sync\Helper\Config $helperConfig
     * @param \Cordial\Sync\Model\Sync $sync
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Cordial\Sync\Helper\Config $helperConfig,
        \Cordial\Sync\Model\Sync $sync,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helperConfig = $helperConfig;
        $this->productFactory = $productFactory;
        $this->sync = $sync;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            // Get initial data from request
            $startId = (int)$this->getRequest()->getParam('startId');
            $storeId = (int)$this->getRequest()->getParam('store');
            $result = ['status' => 'success', 'sync' => true];
            $sync = true;

            $collection = $this->productFactory->create()->getCollection();
            $collection->addStoreFilter($storeId);
            $syncAttr = $this->helperConfig->getSyncAttrCode();
            $collection->addAttributeToFilter($syncAttr, [['null' => true], ['eq' => 1]], 'left');
            $collection->addAttributeToFilter('entity_id', ['gt' => $startId]);
            $collection->setPageSize(\Cordial\Sync\Model\Api\Config::SYNC_STEP_SIZE);
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $startId = $item->getId();
                    $syncEntity = $this->sync->_syncEntity($item->getId(), \Magento\Catalog\Model\Product::ENTITY, $storeId);
                    if (!$syncEntity) {
                        $sync = false;
                    }
                }
                $result = ['status' => 'success', 'sync' => $sync, 'startId' => $startId];
                return $this->jsonResponse($result);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $result = ['status' => 'error'];
        } catch (\Exception $e) {
            $result = ['status' => 'error'];
            $this->logger->critical($e);
        }

        return $this->jsonResponse($result);
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
