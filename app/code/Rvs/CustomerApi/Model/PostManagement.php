<?php
namespace Rvs\CustomerApi\Model;
use Rvs\CustomerApi\Api\PostManagementInterface;
class PostManagement implements PostManagementInterface
{
    /**
     * {@inheritdoc}
     */
    public function customGetMethod()
    {
    
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$customerFactory = $objectManager->create('Magento\Customer\Model\CustomerFactory')->create();
	$customerCollection = $customerFactory->getCollection()
		->addAttributeToSelect("*")
		->load();

	if ($customerCollection && count($customerCollection) > 0) {
	$response = array();
        try{
        foreach ($customerCollection AS $customer) {
            $response[] = [
                    'email' => $customer->getEmail(),
                    'gclid' => $customer->getGclid(),
                    'msclkid' => $customer->getMsclkid()
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
    public function customPostMethod($storeid,$name,$city)
    {
        try{
            $response = [
                'storeid' => $storeid,
                'name' =>$name,
                'city'=>$city
            ];
        }catch(\Exception $e) {
            $response=['error' => $e->getMessage()];
        }
        return json_encode($response);
    }
}
