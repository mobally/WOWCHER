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


namespace Cordial\Sync\Observer\Product;

use Cordial\Sync\Model\Sync;
use \Magento\Framework\App\State;

class SaveAfter extends Sync implements \Magento\Framework\Event\ObserverInterface
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
    
        $success = true;
        $product = $observer->getEvent()->getProduct();

        $sync = $product->getData($this->config->getSyncAttrCode());
        if ($product->getStoreId()) {
            $storeIds = [$product->getStoreId()];
        } else {
            $storeIds = $product->getStoreIds();
        }

        foreach ($storeIds as $storeId) {
            if ($sync) {
                $res = $this->_syncEntity($product, \Magento\Catalog\Model\Product::ENTITY, $storeId, true);
            } else {
                $res = $this->_unsyncEntity($product, \Magento\Catalog\Model\Product::ENTITY, $storeId, true);
            }

            if (!$res) {
                $success = false;
            }
        }

        if (!$success) {
            /** @var  $appState \Magento\Framework\App\State */
            $appState = $this->objectManager->get(\Magento\Framework\App\State::class);
            if ($appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                if ($sync) {
                    $this->messageManager->addErrorMessage(__('Cordial sync issue, please check log.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Cordial unsync issue, please check log.'));
                }
            }
        }

        return $observer;
    }
}
