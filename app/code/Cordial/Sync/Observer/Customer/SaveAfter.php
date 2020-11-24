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

namespace Cordial\Sync\Observer\Customer;

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
        $params = $this->request->getParams();
        $controllerName = $this->request->getControllerName();
        $actionName     = $this->request->getActionName();

        // If mass action 'Change Cordial Sync' we need ignore this event
        if (isset($params['namespace']) && $params['namespace'] == "customer_listing" && isset($params['todo'])) {
            return $observer;
        }

        // If create account action, need to ignore this event
        if ($controllerName == 'account' && $actionName == 'createpost' && @$params['is_subscribed']) {
          return $observer;
        }

        $customer = $observer->getEvent()->getCustomer();
        $sync = $customer->getData($this->config->getSyncAttrCode());
        if (isset($params['customer'])) {
            if ($params['customer'][$this->config->getSyncAttrCode()] != $customer->getData($this->config->getSyncAttrCode())) {
                $sync = $params['customer'][$this->config->getSyncAttrCode()];
            }
        }

        $success = true;
        $storeManager = $this->objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
        $stores = $storeManager->getStores();
        $storesInWebsite = [];
        foreach ($stores as $store) {
            $storesInWebsite[$store->getWebsiteId()][] = $store->getId();
        }
        $storeIds = $storesInWebsite[$customer->getWebsiteId()];
        foreach ($storeIds as $storeId) {
            if ($sync) {
                $res = $this->_syncEntity($customer, \Magento\Customer\Model\Customer::ENTITY, $storeId, true);
            } else {
                $res = $this->_unsyncEntity($customer, \Magento\Customer\Model\Customer::ENTITY, $storeId, true);
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
    }
}

