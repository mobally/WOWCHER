<?php
namespace Rvs\ExpiryProduct\Plugin;

class ProductUrl 
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
     \Magento\Store\Model\StoreManagerInterface $storeManager       
    ){

        $this->storeManager = $storeManager;
    }
    public function beforeGetUrl(
            \Magento\Catalog\Model\Product\Url $subject,
            \Magento\Catalog\Model\Product $product,
            $params = []
    ) {
		$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
		$url = $urlInterface->getCurrentUrl();
		$word = "gclid";
		if(strpos($url, $word) !== false){
			$url = $urlInterface->getCurrentUrl();
			$gclidValue ='';
			$gclidsplit = explode("gclid=",$url);	
			$gclidValue = isset($gclidsplit[1]) ? $gclidsplit[1]:'null';
			
			if(empty($params) || (!empty($params) && !array_key_exists('_query', $params))){
				
				if(!array_key_exists('_query', $params)){
					$params['_query'] = [];
				}
				if(!array_key_exists('gclid', $params['_query'])){
					 $params['_query']['gclid'] = $gclidValue;
				}

			}
		} 
		
		
        return [
            $product,
            $params
        ];
    }
}