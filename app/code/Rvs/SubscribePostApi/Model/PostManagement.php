<?php
namespace Rvs\SubscribePostApi\Model;
use Rvs\SubscribePostApi\Api\PostManagementInterface;
class PostManagement implements PostManagementInterface
{
    
    protected $subscriberFactory;

public function __construct(
    \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
) {
    $this->subscriberFactory= $subscriberFactory;
  
}
    
    
    public function customPostMethod($storeid,$email,$dob,$optin_url,$postcode,$co_sponsor,$living_social,$c_firstname,$c_lastname)
    {
        try{
                $response = $this->subscriberFactory->create()->subscribe($email);
                
               $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
	        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		 $connection = $resource->getConnection();
		$sql = "Update newsletter_subscriber set store_id = '$storeid',optin_url='$optin_url',dob='$dob',postcode='$postcode',co_sponsor='$co_sponsor',living_social='$living_social',
		c_firstname='$c_firstname',c_lastname='$c_lastname' where subscriber_email = '$email'";
		$connection->query($sql);
               
        }catch(\Exception $e) {
            $response=['error' => $e->getMessage()];
        }
        return json_encode($response);
    }
}
