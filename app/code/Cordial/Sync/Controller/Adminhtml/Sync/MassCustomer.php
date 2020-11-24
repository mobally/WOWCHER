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

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use \Magento\Customer\Api\CustomerRepositoryInterface;

class MassCustomer extends \Magento\Backend\App\Action
{

    /*
     * @var \Cordial\Sync\Model\Sync
     */
    protected $sync = null;

    /**
     * @var Filter
     */
    protected $filter;

    /*
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Cordial\Sync\Model\Sync $sync
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Cordial\Sync\Model\Sync $sync,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->sync = $sync;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('customer/index/index');
    }

    /**
     * Customer mass sync/unsync action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $todo = $this->getRequest()->getParam('todo');
        $storeManager = $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $stores = $storeManager->getStores();
        $storesInWebsite = [];
        foreach ($stores as $store) {
            $storesInWebsite[$store->getWebsiteId()][] = $store->getId();
        }

        $customersUpdated = 0;
        foreach ($collection->getAllIds() as $customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customer->setCustomAttribute('cordial_sync', $todo);
            $this->customerRepository->save($customer);

            $storeIds = $storesInWebsite[$customer->getWebsiteId()];
            foreach ($storeIds as $storeId) {
                if ($todo) {
                    $res = $this->sync->_syncEntity($customerId, \Magento\Customer\Model\Customer::ENTITY, $storeId);
                } else {
                    $res = $this->sync->_unsyncEntity($customerId, \Magento\Customer\Model\Customer::ENTITY, $storeId);
                }
            }

            $customersUpdated++;
        }

        if ($customersUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $customersUpdated));
        }

        return $this->resultRedirectFactory->create()->setPath('customer/index/index');
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
