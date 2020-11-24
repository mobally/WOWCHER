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

class ProductCommitBefore extends Sync implements \Magento\Framework\Event\ObserverInterface
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
    
        try {
            //$this->_eventManager->dispatch('catalog_product_import_bunch_delete_after', ['adapter' => $this, 'bunch' => $bunch]);
            $ids = $observer->getEvent()->getIdsToDelete();
            foreach ($ids as $id) {
                $this->_deleteEntity($id, \Magento\Catalog\Model\Product::ENTITY);
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return $observer;
    }
}
