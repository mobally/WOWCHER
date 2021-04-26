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

namespace Cordial\Sync\Model\Api;

class Subscriber extends Client
{

    /**
     * Subscribe
     *
     * @param string $subscriber
     * @param int $storeId
     * @return boolean
     */
    public function setSubscriberStatus($subscriber)
    {
        
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
	$storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
	$storeID       = $storeManager->getStore()->getStoreId(); 
        $path = "contacts";
        switch ($subscriber->getStatus()) {
            case \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED:
                $subscribeStatus = 'subscribed';
                break;

            case \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED:
                $subscribeStatus = 'unsubscribed';
                break;
            default:
                $subscribeStatus = 'none';
        }

        $data = [
            'channels' => [
                'email' => [
                    'address' => $subscriber->getSubscriberEmail(),
                    'subscribeStatus' => $subscribeStatus
                ]
            ],

        ];
        
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
	$catalogSession = $objectManager->get('\Magento\Catalog\Model\Session');
    	$api_session = $catalogSession->getStoreId();
       if($api_session){
       $storeID = $api_session;
       }
        if ($subscribeStatus == 'subscribed') {
            $data['forceSubscribe'] = true;
            if($storeID == 4){
            $data['Belgium'] = true;
            }else if($storeID == 3)
            {
            $data['Spain'] = true;
            }
            else if($storeID == 2)
            {
            $data['Poland'] = true;
            }
            else{
            $data['promotional'] = true;
            }
        }

        $purchaseDate = $this->getLastPurchaseByEmail($subscriber->getSubscriberEmail());
        if ($purchaseDate) {
            $dateTime = new \DateTime($purchaseDate);
            $createdAt = $dateTime->format(\Cordial\Sync\Model\Api\Config::DATE_FORMAT);
            $data['lastPurchase'] = $createdAt;
        }

        $result = false;
        if ($subscriber->getCustomerId()) {
            $entity = $this->objectManager->create(\Magento\Eav\Model\Entity::class);
            $entityTypeId = $entity->setType('customer')->getTypeId();
            $touchedModel = $this->objectManager->create(\Cordial\Sync\Model\Touched::class);
            $touched = $touchedModel->loadByUniqueKey($subscriber->getCustomerId(), $entityTypeId, $this->storeId);
            if ($touched->getExternalId()) {
                $pathUpdate = $path . '/' . rawurlencode($touched->getExternalId());
                $result = $this->_request('PUT', $pathUpdate, $data);
            }
        }

        if (!$result) {
            $result = $this->_request('POST', $path, $data);
        }

        if (!$result) {
            return false;
        }
$catalogSession->unsStoreId();
        return true;
    }

    /**
     * Get Subscriber by Email
     *
     * @param string $email
     * @return array
     */
    public function getByEmail($email, $storeId = null)
    {
        $data = [];
        $setApiKey = $this->_setApiKey($storeId);
        $path = "contacts";
        $data['email'] = $email;
        $result = $this->_request('POST', $path, $data);
        return $result;
    }

    /**
     * Delete Subscriber or Set contact status to none
     *
     * @param string $email
     * @param int $storeId
     * @return boolean
     */
    public function delete($email)
    {
        $path = "contacts";

        $data = [
            'channels' => [
                'email' => [
                    'address' => $email,
                    'subscribeStatus' => 'none'
                ]
            ],

        ];

        $result = $this->_request('POST', $path, $data);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Return last purchase date.
     *
     * @param  $email
     * @param int $storeId
     * @return string
     */
    protected function getLastPurchaseByEmail($email)
    {
        $purchaseDate = '';
        $collection = $this->objectManager->create(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class)->create();
        $order = $collection->addFieldToFilter('store_id', $this->storeId)
            ->addFieldToFilter('customer_email', $email)
            ->setOrder('created_at', 'DESC')
            ->getFirstItem();

        if ($order) {
            $purchaseDate = $order->getCreatedAt();
        }

        return $purchaseDate;
    }
}
