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
    
        $product = $observer->getProduct();

        $res = $this->_deleteEntity($product, \Magento\Catalog\Model\Product::ENTITY);

        if (!$res) {
            $this->messageManager->addErrorMessage(__('Cordial delete issue, please check log.'));
        }

        return $observer;
    }
}
