<?php
namespace Rvs\CustomerApi\Model;
use Rvs\CustomerApi\Api\PostManagementInterface;
class PostManagement implements PostManagementInterface
{
    /**
     * {@inheritdoc}
     */
     protected $subscriberCollection;
     
     public function __construct(
    \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection
) {
    $this->subscriberCollection = $subscriberCollection;
}
     
    public function customGetMethod($pagesize,$currentpage)
    {
    
    $subscriberCollection = $this->subscriberCollection->create();
    $subscriberCollection->setPageSize($pagesize);
    $subscriberCollection->setCurPage($currentpage);
    if ($subscriberCollection && count($subscriberCollection) > 0) {
	$response = array();
        try{
        foreach ($subscriberCollection AS $subscriber) {
            $response[] = [
            	     'customer_id' => $subscriber->getSubscriberId(),
                    'email' => $subscriber->getSubscriberEmail(),
                    'gclid' => $subscriber->getGclid(),
                    'ito' => $subscriber->getIto(),
                    'store_id' => $subscriber->getStoreId(),
                    'created_date' => $subscriber->getLocalTime(),
                    'updated_date' => $subscriber->getChangeStatusAt(),
                    
            ];
        }
        }catch(\Exception $e) {
            $response=['error' => $e->getMessage()];
        }
   }
        return json_encode($response);
    }
    /**
     * {@inheritdoc}
     */
    
}
