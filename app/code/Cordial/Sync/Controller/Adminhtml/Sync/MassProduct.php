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

class MassProduct extends \Magento\Backend\App\Action
{

    /**
     * MassActions filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /*
     * @var \Cordial\Sync\Model\Sync
     */
    protected $sync = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Cordial\Sync\Model\Sync $sync
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Cordial\Sync\Model\Sync $sync,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;

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
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $todo = (int)$this->getRequest()->getParam('todo');
        $filters = (array)$this->getRequest()->getParam('filters', []);

        /** var $collection \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();

        if (isset($filters['store_id'])) {
            $storeId = (int)$filters['store_id'];
        }

        try {
            if ($storeId) {
                $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
                    ->updateAttributes($productIds, ['cordial_sync' => $todo], $storeId);

                foreach ($productIds as $productId) {
                    if ($todo) {
                        $res = $this->sync->_syncEntity($productId, \Magento\Catalog\Model\Product::ENTITY, $storeId);
                    } else {
                        $res = $this->sync->_unsyncEntity($productId, \Magento\Catalog\Model\Product::ENTITY, $storeId);
                    }
                }
            } else {
                foreach ($collection as $product) {
                    $storeIds = $product->getStoreIds();
                    foreach ($storeIds as $storeId) {
                        $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
                            ->updateAttributes($productIds, ['cordial_sync' => $todo], $storeId);

                        if ($todo) {
                            $res = $this->sync->_syncEntity($product->getId(), \Magento\Catalog\Model\Product::ENTITY, $storeId);
                        } else {
                            $res = $this->sync->_unsyncEntity($product->getId(), \Magento\Catalog\Model\Product::ENTITY, $storeId);
                        }
                    }
                }
            }

            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', count($productIds)));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while updating the product(s) Cordial sync.'));
        }

        return $this->resultRedirectFactory->create()->setPath('catalog/product/index', ['store' => $storeId]);
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
