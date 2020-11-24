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


namespace Cordial\Sync\Controller\Adminhtml\Unsync;

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
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Cordial\Sync\Model\Sync $sync,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
    
        $this->sync = $sync;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('selected');

        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $order = $this->orderRepository->get($itemId);
                    $order->setCordialSync(0);
                    $order->save();
                    $unsyncEntity = $this->sync->_unsyncEntity($order, \Cordial\Sync\Model\Api\ApiFactory::API_ORDER, $order->getStoreId());
                    if (!$unsyncEntity) {
                        $sync = false;
                    }
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) unsync.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return $this->resultRedirectFactory->create()->setPath('sales/order/index');
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
