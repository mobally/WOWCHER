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

namespace Cordial\Sync\Model\Import;

class Customer extends \Magento\CustomerImportExport\Model\Import\Customer
{

    protected function _cordialSaveCustomer(array $entitiesToCreate, array $entitiesToUpdate)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $idsToSync = [];
        /** @var  $sync \Cordial\Sync\Model\Sync */
        $sync = $objectManager->create(\Cordial\Sync\Model\Sync::class);
        foreach ($entitiesToCreate as $entity) {
            $idsToSync[] = $entity['entity_id'];
        }
        foreach ($entitiesToUpdate as $entity) {
            $idsToSync[] = $entity['entity_id'];
        }

        if (!empty($idsToSync)) {
            $stores = $this->_storeManager->getStores();
            $storesInWebsite = [];
            foreach ($stores as $store) {
                $storesInWebsite[$store->getWebsiteId()][] = $store->getId();
            }
            /** @var  $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
            $collection= $objectManager->create(\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class)->create();
            $collection->addFieldToFilter('entity_id', ['in' => $idsToSync]);
            $collection->addAttributeToFilter(\Cordial\Sync\Model\Api\Config::ATTR_CODE, [['null' => true], ['eq' => 1]], 'left');
            //$collection->load();
            foreach ($collection as $item) {
                $storeIds = $storesInWebsite[$item->getWebsiteId()];
                foreach ($storeIds as $storeId) {
                    $sync->_syncEntity($item->getId(), \Magento\Customer\Model\Customer::ENTITY, $item->getStoreId());
                }
            }
        }
        return $this;
    }

    protected function _cordialDeleteCustomer(array $entitiesToDelete)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var  $sync \Cordial\Sync\Model\Sync */
        $sync = $objectManager->create(\Cordial\Sync\Model\Sync::class);
        foreach ($entitiesToDelete as $id) {
            $sync->_deleteEntity($id, \Magento\Customer\Model\Customer::ENTITY, null);
        }
        return $this;
    }

    /**
     * Import data rows
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToCreate = [];
            $entitiesToUpdate = [];
            $entitiesToDelete = [];
            $attributesToSave = [];

            foreach ($bunch as $rowNumber => $rowData) {
                if (!$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
                    $entitiesToDelete[] = $this->_getCustomerId(
                        $rowData[self::COLUMN_EMAIL],
                        $rowData[self::COLUMN_WEBSITE]
                    );
                } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
                    $processedData = $this->_prepareDataForUpdate($rowData);
                    $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
                    $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
                    foreach ($processedData[self::ATTRIBUTES_TO_SAVE_KEY] as $tableName => $customerAttributes) {
                        if (!isset($attributesToSave[$tableName])) {
                            $attributesToSave[$tableName] = [];
                        }
                        $attributesToSave[$tableName] = array_diff_key(
                            $attributesToSave[$tableName],
                            $customerAttributes
                        ) + $customerAttributes;
                    }
                }
            }
            $this->updateItemsCounterStats($entitiesToCreate, $entitiesToUpdate, $entitiesToDelete);
            /**
             * Save prepared data
             */
            if ($entitiesToCreate || $entitiesToUpdate) {
                $this->_saveCustomerEntities($entitiesToCreate, $entitiesToUpdate);
            }
            if ($attributesToSave) {
                $this->_saveCustomerAttributes($attributesToSave);
            }
            if ($entitiesToDelete) {
                $this->_deleteCustomerEntities($entitiesToDelete);
            }
            if ($entitiesToCreate || $entitiesToUpdate) {
                $this->_cordialSaveCustomer($entitiesToCreate, $entitiesToUpdate);
            }
            if ($entitiesToDelete) {
                $this->_cordialDeleteCustomer($entitiesToDelete);
            }
        }

        return true;
    }
}
