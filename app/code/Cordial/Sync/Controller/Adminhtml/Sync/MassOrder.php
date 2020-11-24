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

class MassOrder extends \Magento\Backend\App\Action
{
    /*
     * @var \Cordial\Sync\Model\Sync
     */
    protected $sync = null;

    /*
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;


    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Cordial\Sync\Model\Sync $sync
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Cordial\Sync\Model\Sync $sync,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->sync = $sync;
        $this->orderRepository = $orderRepository;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
        $todo = $this->getRequest()->getParam('todo');
        $filters = (array)$this->getRequest()->getParam('filters', []);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $itemIds = $collection->getAllIds();

        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select order(s).'));
        } else {
            try {
                if ($todo == \Cordial\Sync\Model\Touched::NEED_SYNC_YES) {
                    $this->syncOrder($itemIds);
                    $todoLabel = __('sync');
                }
                if ($todo == \Cordial\Sync\Model\Touched::NEED_SYNC_UNSYNC) {
                    $this->unsyncOrder($itemIds);
                    $todoLabel = __('unsync');
                }

                $this->messageManager->addSuccess(
                    __('A total of %1 order(s) %1.', count($itemIds), $todoLabel)
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->addException($e, __('Something went wrong while updating the order(s) Cordial sync.'));
            }
        }

        return $this->resultRedirectFactory->create()->setPath('sales/order/index');
    }

    protected function syncOrder($itemIds)
    {
        foreach ($itemIds as $itemId) {
            $order = $this->orderRepository->get($itemId);
            $order->setCordialSync(1);
            $order->save();
            $syncEntity = $this->sync->_syncEntity($order, \Magento\Sales\Model\Order::ENTITY, $order->getStoreId());
        }
    }

    protected function unsyncOrder($itemIds)
    {
        foreach ($itemIds as $itemId) {
            $order = $this->orderRepository->get($itemId);
            $order->setCordialSync(0);
            $order->save();
            $unsyncEntity = $this->sync->_unsyncEntity($order, \Cordial\Sync\Model\Api\ApiFactory::API_ORDER, $order->getStoreId());
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        if ($this->_authorization->isAllowed('Cordial_Sync::sync_configuration') || $this->_authorization->isAllowed('Cordial_Sync::sync_config')) {
            return true;
        }

        return false;
    }
}
