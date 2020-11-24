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

class SaveBefore extends Sync implements \Magento\Framework\Event\ObserverInterface
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
        if (isset($params['namespace']) && $params['namespace'] == "customer_listing" && isset($params['todo'])) {
            return $observer;
        }

        $attrCode = $this->config->getSyncAttrCode();
        $customer = $observer->getEvent()->getCustomer();
        if (isset($params['customer'][$attrCode])) {
            $customer->setData($attrCode, $params['customer'][$attrCode]);
        }
    }
}
