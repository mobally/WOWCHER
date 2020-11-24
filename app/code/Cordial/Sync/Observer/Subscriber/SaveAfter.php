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

namespace Cordial\Sync\Observer\Subscriber;

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
    
        // If mass action 'Change Cordial Sync' we need ignore this event
        $params = $this->request->getParams();
        if (isset($params['namespace']) && $params['namespace'] == "customer_listing" && isset($params['todo'])) {
            return $observer;
        }

        $subscriber = $observer->getEvent()->getSubscriber();
        if ($subscriber->getStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE
            || $subscriber->getStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_UNCONFIRMED
        ) {
            return $observer;
        }
        $storeId = $subscriber->getStoreId();
        if (!$this->config->isEnabled($storeId)) {
            return $observer;
        }

        $subscriberApi = $this->objectManager->create(\Cordial\Sync\Model\Api\Subscriber::class);
        $subscriberApi->load($storeId);
        $res = $subscriberApi->setSubscriberStatus($subscriber);
        if (!$res) {
            /** @var  $appState \Magento\Framework\App\State */
            $appState = $this->objectManager->get(\Magento\Framework\App\State::class);
            if ($appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                $this->messageManager->addErrorMessage(__('Cordial subscribe issue, please check log.'));
            }
        }

        return $observer;
    }
}
