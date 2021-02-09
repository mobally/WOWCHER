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
		$url_components = parse_url( $url, $component = -1 );	
		
		$word = "gclid";
		if(array_key_exists('query', $url_components)){
			
			$query = $url_components['query'];
			$url = $urlInterface->getCurrentUrl();
			$gclidValue ='';
			$querysplit = explode("&",$query);	
			//$gclidValue = isset($gclidsplit[1]) ? $gclidsplit[1]:'null';
			
			if(empty($params) || (!empty($params) && !array_key_exists('_query', $params))){
				
				if(!array_key_exists('_query', $params)){
					$params['_query'] = [];
				}
			foreach($querysplit as $splitquery){
				$quermain =  explode("=",$splitquery);
				$queryname = $quermain[0];
				$queryvalue = $quermain[1];
				
				if(!array_key_exists($queryname, $params['_query'])){
					 $params['_query'][$queryname] = $queryvalue;
				}

			}
			}			
		} 
		
        return [
            $product,
            $params
        ];
    }
}
