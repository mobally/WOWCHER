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

use Cordial\Sync\Api\Data\TouchedInterface;

class Touched extends \Magento\Framework\Model\AbstractModel implements TouchedInterface
{
    const STATUS_SYNC = 1;
    const STATUS_UNSYNC = 2;

    const NEED_SYNC_NO = 0;
    const NEED_SYNC_YES = 1;
    const NEED_SYNC_UNSYNC = 2;
    const NEED_SYNC_DELETE = 3;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Cordial\Sync\Model\ResourceModel\Touched::class);
    }

    /**
     * Get touched_id
     * @return string
     */
    public function getTouchedId()
    {
        return $this->getData(self::TOUCHED_ID);
    }

    /**
     * Set touched_id
     * @param string $touchedId
     * @return \Cordial\Sync\Api\Data\TouchedInterface
     */
    public function setTouchedId($touchedId)
    {
        return $this->setData(self::TOUCHED_ID, $touchedId);
    }

    /**
     * Sync
     *
     * @param $entityId
     * @param $storeId
     * @param $entityTypeId
     * @return Cordial_Sync_Model_Touched
     */
    public function sync($entityId, $entityTypeId, $storeId)
    {
        $item = $this->loadByUniqueKey($entityId, $entityTypeId, $storeId);
        if ($item->getId()) {
            $this->setId($item->getId());
        }
        $this->setEntityId($entityId);
        $this->setEntityTypeId($entityTypeId);
        $this->setStoreId($storeId);
        $this->setStatus(self::STATUS_SYNC);
        $this->setTodo(self::NEED_SYNC_NO);
        $this->save();

        return $this;
    }

    /**
     * UnSync
     *
     * @param $entityId
     * @param $storeId
     * @param $entityTypeId
     * @return Cordial_Sync_Model_Touched
     */
    public function unsync($entityId, $entityTypeId, $storeId)
    {
        $item = $this->loadByUniqueKey($entityId, $entityTypeId, $storeId);
        if ($item->getId()) {
            $this->setId($item->getId());
        }
        $this->setEntityId($entityId);
        $this->setEntityTypeId($entityTypeId);
        $this->setStoreId($storeId);
        $this->setStatus(self::STATUS_UNSYNC);
        $this->setTodo(self::NEED_SYNC_NO);
        $this->save();

        return $this;
    }

    /**
     * Load by Unique Key
     * @param $entityId
     * @param $storeId
     * @param $entityTypeId
     * @return Cordial_Sync_Model_Touched
     */
    public function loadByUniqueKey($entityId, $entityTypeId, $storeId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('entity_id', $entityId)
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('store_id', $storeId);

        return $collection->getFirstItem();
    }

    public function setNeed($todo, $entityId, $entityTypeId, $storeId)
    {
        $item = $this->loadByUniqueKey($entityId, $entityTypeId, $storeId);
        if ($item->getId()) {
            if ($item->getTodo() == $todo) {
                return $item;
            }
            $this->setId($item->getId());
        }
        $this->isObjectNew(true);
        $this->setEntityId($entityId);
        $this->setEntityTypeId($entityTypeId);
        $this->setStoreId($storeId);
        $this->setTodo($todo);
        $this->save();
        return $this;
    }

    public function getStoreIds($entityId, $entityTypeId)
    {
        $storeIds = [];
        $collection = $this->getCollection()
            ->addFieldToFilter('entity_id', $entityId)
            ->addFieldToFilter('entity_type_id', $entityTypeId);
        foreach ($collection as $item) {
            $storeIds[] = $item->getStoreId();
        }
        return $storeIds;
    }
}
