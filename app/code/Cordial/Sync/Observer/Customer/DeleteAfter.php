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
    
        $customer = $observer->getCustomer();
        $params = $this->request->getParams();
        if (isset($params['namespace']) && $params['namespace'] == "customer_listing") {
            $res = $this->_deleteEntity($customer, \Magento\Customer\Model\Customer::ENTITY);
        } else {
            $res = $this->_deleteEntity($customer, \Magento\Customer\Model\Customer::ENTITY, true);
        }

        if (!$res) {
            $this->messageManager->addErrorMessage(__('Cordial delete issue, please check log.'));
        }

        return $observer;
    }
}
