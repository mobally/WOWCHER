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

namespace Cordial\Sync\Observer\Sales;

use Cordial\Sync\Model\Sync;
use \Magento\Framework\App\State;

class SaveAfter 
    extends Sync 
    implements \Magento\Framework\Event\ObserverInterface
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
        // If mass action 'Change Cordial Sync' we need ignore this event
        $params = $this->request->getParams();
        if (isset($params['namespace']) && $params['namespace'] == "sales_order_grid" && isset($params['todo'])) {
            return $observer;
        }

        $success  = true;
        $order 	  = $observer->getEvent()->getOrder();
        $sync     = $order->getData($this->config->getSyncAttrCode());
        $storeId  = $order->getStoreId();

        if ($sync || $order->getData('state') == "new") {
            $res = $this->_syncEntity($order, \Magento\Sales\Model\Order::ENTITY, $storeId, Sync::SYNC_NONFORCE);
        } else {
            $res = $this->_unsyncEntity($order, \Magento\Sales\Model\Order::ENTITY, $storeId, Sync::SYNC_NONFORCE);
        }

        if (!$res) {
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
