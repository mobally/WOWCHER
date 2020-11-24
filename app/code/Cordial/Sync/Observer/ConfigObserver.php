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

namespace Cordial\Sync\Observer;

use Cordial\Sync\Model\Sync;
use \Magento\Framework\App\State;

class ConfigObserver extends Sync implements \Magento\Framework\Event\ObserverInterface
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
        $storeId = (int)$this->request->getParam('store');
        if (is_null($storeId)) {
            return $observer;
        }

        if (!$this->config->isEnabled($storeId)) {
            return $observer;
        }

        if (@$params['groups']['general']['fields']['api_key']['value']) {
            /* @var $api \Cordial\Sync\Model\Api\Attribute */
            $api = $this->objectManager->create(\Cordial\Sync\Model\Api\Attribute::class);
            $api->load($storeId);

            // Create promotional list if not exist
            $accountlists = $api->getAccountlists($storeId, 'promotional');
            if (!$accountlists) {
                $promotional = $api->createList('promotional', $storeId);
                if (!$promotional) {
                    $this->messageManager->addErrorMessage(__('Can\'t create \'promotional\' attribute'));
                }
            }

            $attributes = $api->getAllAttributes();

            if (!isset($attributes['magentoAlerts'])) {
                $attribute = [
                    'value' => 'magentoAlerts',
                    'type' => 'array',
                ];
                // Create Magento alert for widget
                $magentoAlerts = $api->create($attribute, 'customAttribute', 'magentoAlerts');
                if (!$magentoAlerts) {
                    $this->messageManager->addErrorMessage(__('Can\'t create \'magentoAlerts\' attribute'));
                }
            }

            if (!isset($attributes['m_wishlist'])) {
                $attribute = [
                    'value' => 'm_wishlist',
                    'type' => 'array',
                    'index' => true
                ];
                $wishlist = $api->create($attribute, 'customAttribute', 'm_wishlist');
                if (!$wishlist) {
                    $this->messageManager->addErrorMessage(__('Can\'t create \'m_wishlist\' attribute'));
                }
            }

            if (!isset($attributes['lastPurchase'])) {
                $attribute = [
                    'value' => 'lastPurchase',
                    'type' => 'date',
                    'index' => true
                ];
                $lastPurchase = $api->create($attribute, 'customAttribute', 'lastPurchase');
                if (!$lastPurchase) {
                    $this->messageManager->addErrorMessage(__('Can\'t create \'lastPurchase\' attribute'));
                }
            }

            if (!isset($attributes['magentoDeleted'])) {
                $attribute = [
                    'value' => 'magentoDeleted',
                    'type' => 'date',
                ];
                $magentoDeleted = $api->create($attribute, 'customAttribute', 'magentoDeleted');
                if (!$magentoDeleted) {
                    $this->messageManager->addErrorMessage(__('Can\'t create \'magentoDeleted\' attribute'));
                }
            }
        }
    }
}
