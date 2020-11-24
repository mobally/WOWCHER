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


namespace Cordial\Sync\Observer\Import;

use Cordial\Sync\Model\Sync;
use \Magento\Framework\App\State;

class ProductSaveAfter extends Sync implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
    
        $bunch = $observer->getEvent()->getBunch();
        $skuToSync = [];
        foreach ($bunch as $rowNum => $rowData) {
            $skuToSync[] = $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU];
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productFactory = $objectManager->get(\Magento\Catalog\Model\ProductFactory::class);
        /* @var $productCollection  \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $productFactory->create()->getCollection();
        $productCollection->addAttributeToSelect(\Cordial\Sync\Model\Api\Config::ATTR_CODE, 'left');
        $productCollection->addAttributeToSelect('store_id');
        $productCollection->addAttributeToFilter('sku', $skuToSync);

        foreach ($productCollection as $product) {
            if ($product->getStoreId()) {
                $storeIds = [$product->getStoreId()];
            } else {
                $storeIds = $product->getStoreIds();
            }
            $sync = $product->getData(\Cordial\Sync\Model\Api\Config::ATTR_CODE);
            foreach ($storeIds as $storeId) {
                if ($sync || is_null($sync)) {
                    $res = $this->_syncEntity($product->getId(), \Magento\Catalog\Model\Product::ENTITY, $storeId);
                } else {
                    $res = $this->_unsyncEntity($product->getId(), \Magento\Catalog\Model\Product::ENTITY, $storeId);
                }
            }
        }

        return $observer;
    }
}
