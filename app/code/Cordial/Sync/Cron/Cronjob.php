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

namespace Cordial\Sync\Cron;

use Cordial\Sync\Model\ResourceModel\Touched\CollectionFactory;
use Cordial\Sync\Model\Sync;
use \Cordial\Sync\Model\Api\ApiFactory;

class Cronjob
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /*
     * @var \Cordial\Sync\Model\Sync
     */
    protected $_sync = null;


    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param Sync $sync
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Sync $sync
    ) {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->_sync = $sync;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute() {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('todo', ['neq' => \Cordial\Sync\Model\Touched::NEED_SYNC_NO]);
        $collection->addFieldToFilter('created_at', ['lt' => date('Y-m-d H:i:s', strtotime('-1 minute'))]);
        $collection->setOrder('updated_at', 'ASC');
        $collection->setPageSize(\Cordial\Sync\Model\Api\Config::SYNC_STEP_SIZE);
        $this->logger->debug('Cordial_Sync_Run: ' . $collection->getSelect()->__toString());

        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            try {
                $entity = $item->getEntityId();
                $entityType = $this->_getEntityTypeCode($item->getEntityTypeId());
                $storeId = $item->getStoreId();
                $force = true;

                if ($item->getData('todo') == \Cordial\Sync\Model\Touched::NEED_SYNC_YES) {
                    $this->_sync->_syncEntity($entity, $entityType, $storeId, $force);
                }
                if ($item->getData('todo') == \Cordial\Sync\Model\Touched::NEED_SYNC_UNSYNC) {
                    $this->_sync->_unsyncEntity($entity, $entityType, $storeId, $force);
                }
                if ($item->getData('todo') == \Cordial\Sync\Model\Touched::NEED_SYNC_DELETE) {
                    $this->_sync->_deleteEntity($entity, $entityType, $storeId, $force);
                }
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }

    protected function _getEntityTypeCode($id)
    {

        $entityCode = [];
        $entityCode[ApiFactory::API_CUSTOMER] = $this->_sync->getEntityTypeId(ApiFactory::API_CUSTOMER);
        $entityCode[ApiFactory::API_PRODUCT] = $this->_sync->getEntityTypeId(ApiFactory::API_PRODUCT);
        $entityCode[ApiFactory::API_ORDER] = $this->_sync->getEntityTypeId(ApiFactory::API_ORDER);
        $entityCode = array_flip($entityCode);

        return $entityCode[$id];
    }
}
