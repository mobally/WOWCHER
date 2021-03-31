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

namespace Cordial\Sync\Model;

use Cordial\Sync\Controller\Adminhtml\Touched;

class Sync
{
	const SYNC_FORCE	= true;
	const SYNC_NONFORCE = false;

    /**
     * @var \Cordial\Sync\Helper\Config
     */
    protected $config;

    /**
     * @var \Cordial\Sync\Model\Api\ApiFactory
     */
    protected $apiFactory;

    /**
     * @var \Magento\Eav\Model\Entity
     *
     */
    protected $entity;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;


    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;


    /**
     * @param Api\ApiFactory $apiFactory
     * @param \Cordial\Sync\Helper\Config $config
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Cordial\Sync\Model\Touched $touched
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Message\ManagerInterface $managerInterface
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Cordial\Sync\Model\Api\ApiFactory $apiFactory,
        \Cordial\Sync\Helper\Config $config,
        \Magento\Eav\Model\Entity $entity,
        \Cordial\Sync\Model\Touched $touched,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\RequestInterface $request
    ) {

        $this->apiFactory = $apiFactory;
        $this->config = $config;
        $this->entity = $entity;
        $this->_touched = $touched;
        $this->logger = $logger;
        $this->messageManager = $managerInterface;
        $this->objectManager = $objectManager;
        $this->redirect = $redirect;
        $this->request = $request;
    }


    /**
     * Get api
     *
     * @param string $name
     * @return \Cordial\Sync\Model\Api\Product|\Cordial\Sync\Model\Api\Customer|Cordial\Sync\Model\Api\Order
     */
    protected function getApi($name)
    {
        return $this->apiFactory->create($name);
    }

    /**
	 * Notes: Mon Oct 19 12:34:58 2020
        - Products, Customers, Orders load by batch 40 items, and for each item (product, order) 
		request for update saved into table [cordial_sync_touched]

		- By several batch iterations, all items (products) saved into request for update table [cordial_sync_touched]

		- Later by Cronjob, products, orders, customers, synced with Cordial by batch 40 items 
	**/
    public function _syncEntity($entity, $entityType, $storeId, $force = false)
    {
        if (is_numeric($entity)) {
            $entityId = $entity;
        } else {
            $entityId = $entity->getId();
        }

        $entityTypeId	= $this->getEntityTypeId($entityType);
        $showMessage	= false;
        $this->logger->debug('*** _syncEntity/Model/Sync: ' . $entityId . ':' . $entityType . ':' 
            . $force . ':' . $this->config->syncImmediately());

        try {
            if (!$this->config->isEnabled($storeId)) {
                return true;
            }

            $externalId = null;
            $touched	= $this->objectManager->create('Cordial\Sync\Model\Touched')
				->setNeed(\Cordial\Sync\Model\Touched::NEED_SYNC_YES, $entityId, $entityTypeId, $storeId);

            if (!$this->config->syncImmediately() && !$force) {
                return true;
            }

            $api = $this->getApi($entityType)->load($storeId);

            if ($touched instanceof \Cordial\Sync\Model\Touched && $touched->getStatus() == \Cordial\Sync\Model\Touched::STATUS_SYNC) {
                $sync = $api->update($entity);
            } else {
                $sync = $api->create($entity);
            }

            if (!$sync) {
                // update updated_at for cron queue
                // remove failed sync from further queue
        	    $touched->setTodo(0);
                $touched->save();
                return false;
            }

            $dataTouched = $this->objectManager->create('Cordial\Sync\Model\Touched')
				->sync($entityId, $entityTypeId, $storeId, $externalId);
        } catch (\Exception $e) {
            $this->config->debug("Cordial: Issue with $entityType ID $entityId");
            $this->config->debug($e->getMessage());
            return false;
        }

        return true;
    }

    public function _unsyncEntity($entity, $entityType, $storeId, $force = false)
    {
        if (is_numeric($entity)) {
            $entityId = $entity;
        } else {
            $entityId = $entity->getData('entity_id');
        }

        $entityTypeId = $this->getEntityTypeId($entityType);

        try {
            if (!$this->config->isEnabled($storeId)) {
                return true;
            }

            $dataTouched = $this->objectManager->create('Cordial\Sync\Model\Touched')->unsync($entityId, $entityTypeId, $storeId);
        } catch (\Exception $e) {
            $this->config->debug("Cordial: Issue with $entityType ID $entityId");
            $this->config->debug($e->getMessage());
            return false;
        }

        return true;
    }

    public function _deleteEntity($entity, $entityType, $force = false)
    {
        if (is_numeric($entity)) {
            $entityId = $entity;
        } else {
            $entityId = $entity->getData('entity_id');
            $storeManager = $this->objectManager->create('Magento\Store\Model\StoreManagerInterface');
            $stores = $storeManager->getStores();
            $storesInWebsite = [];
            foreach ($stores as $store) {
                $storesInWebsite[$store->getWebsiteId()][] = $store->getId();
            }
            $storeIds = $storesInWebsite[$entity->getWebsiteId()];
        }
        $entityTypeId = $this->getEntityTypeId($entityType);
        $success = true;
        if (!isset($storeIds)) {
            $storeIds = $this->objectManager->create('Cordial\Sync\Model\Touched')->getStoreIds($entityId, $entityTypeId);
        }

        foreach ($storeIds as $storeId) {
            $res = $this->_deleteEntityForStore($entity, $entityType, $storeId, $force);
            if (!$res) {
                $success = false;
            }
        }
        return $success;
    }

    public function _deleteEntityForStore($entity, $entityType, $storeId, $force = false)
    {
        if (is_numeric($entity)) {
            $entityId = $entity;
        } else {
            $entityId = $entity->getData('entity_id');
        }
        $entityTypeId = $this->getEntityTypeId($entityType);
        $showMessage = false;

        try {
            if (!$this->config->isEnabled($storeId)) {
                return true;
            }
            $touched = $this->objectManager->create('Cordial\Sync\Model\Touched')->setNeed(\Cordial\Sync\Model\Touched::NEED_SYNC_DELETE, $entityId, $entityTypeId, $storeId);
            //Fix for delete contact that removed from sync table
            if ($entityTypeId == 1) {
                if (!$touched->getExternalId()) {
                    if (is_object($entity) && $entity->getEmail()) {
                        $touched->setExternalId($entity->getEmail());
                    }
                    $touched->save();
                }
            }

            if (!$this->config->syncImmediately($storeId) && !$force) {
                return true;
            }
            $api = $this->getApi($entityType)->load($storeId);
            $del = $api->delete($entity);
            if (!$del) {
                if ($touched->getStatus() == \Cordial\Sync\Model\Touched::STATUS_SYNC) {
                    return false;
                }
            }
            $dataTouched = $this->objectManager->create('Cordial\Sync\Model\Touched')->loadByUniqueKey($entityId, $entityTypeId, $storeId);
            if (is_object($dataTouched)) {
                $dataTouched->delete();
            }
        } catch (\Exception $e) {
            $this->config->debug("Cordial: Issue with $entityType ID $entityId");
            $this->config->debug($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Get entity type id
     *
     * @return int
     */
    public function getEntityTypeId($code)
    {
        return $this->entity->setType($code)->getTypeId();
    }
}
