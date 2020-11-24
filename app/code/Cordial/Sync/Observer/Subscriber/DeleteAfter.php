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

class DeleteAfter extends Sync implements \Magento\Framework\Event\ObserverInterface
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
        $subscriber = $observer->getEvent()->getSubscriber();
        $storeId = $subscriber->getStoreId();

        if ($storeId == 0 || !$this->config->isEnabled($storeId)) {
            return $observer;
        }

        /** @var  $subscriberApi \Cordial\Sync\Model\Api\Subscriber */
        $subscriberApi = $this->objectManager->create(Cordial\Sync\Model\Api\Subscriber::class);
        $subscriberApi->load($storeId);
        $res = $subscriberApi->delete($subscriber->getSubscriberEmail());

        return $observer;
    }
}
