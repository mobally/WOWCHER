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

class Order extends \Magento\Backend\App\Action
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Cordial\Sync\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /*
     * @var \Cordial\Sync\Model\Sync
     */
    protected $sync = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Cordial\Sync\Model\Sync $sync
     * @param \Cordial\Sync\Helper\Data $helperData
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Cordial\Sync\Model\Sync $sync,
        \Cordial\Sync\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helperData = $helperData;
        $this->orderCollectionFactory = $orderCollectionFactory;
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
            $startId = $this->getRequest()->getParam('startId');
            $storeId = $this->getRequest()->getParam('store');
            $result = ['status' => 'success', 'sync' => true];
            $sync = true;

            $collection = $this->orderCollectionFactory->create();
            $collection->addFieldToFilter('store_id', $storeId);
            $syncAttr = $this->helperData->getSyncAttrCode();
            $collection->addAttributeToFilter($syncAttr, [['null' => true], ['eq' => 1]], 'left');
            $collection->addAttributeToFilter('entity_id', ['gt' => $startId]);
            $perPage = \Cordial\Sync\Model\Api\Config::SYNC_STEP_SIZE;
            $collection->setPageSize($perPage);

            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $startId = $item->getId();
                    $syncEntity = $this->sync->_syncEntity($item->getId(), \Magento\Sales\Model\Order::ENTITY, $storeId);
                    if (!$syncEntity) {
                        $sync = false;
                    }
                }
                $result = ['status' => 'success', 'sync' => $sync, 'startId' => $startId];
                return $this->jsonResponse($result);
            }

            $result = ['status' => 'success', 'sync' => $sync];
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
