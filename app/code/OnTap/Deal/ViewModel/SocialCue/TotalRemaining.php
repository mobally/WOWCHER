<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Deal\ViewModel\SocialCue;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use OnTap\Deal\ViewModel\SocialCueInterface;

class TotalRemaining implements SocialCueInterface
{
    const TOTAL_REMAINING = 'total_remaining';
    const TOTAL_BOUGHT = 'total_bought';

    /**
     * @var Grouped
     */
    protected Grouped $grouped;

    /**
     * @var StockRegistryInterface
     */
    protected StockRegistryInterface $stockRegistry;

    /**
     * TotalRemaining constructor.
     * @param Grouped $grouped
     * @param StockRegistryInterface $stockRegistry
     */
     
	     /**
	 * @var \Magento\Framework\HTTP\Client\Curl
	 */
    protected $_curl;

protected $_storeManager;

    public function __construct(
        Grouped $grouped,
        StockRegistryInterface $stockRegistry,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->grouped = $grouped;
        $this->stockRegistry = $stockRegistry;
        $this->_curl = $curl;
    	$this->_storeManager = $storeManager; 
    }

    /**
     * @inheritDoc
     */
    public function canShow(ProductInterface $product): bool
    {
        return true;
    }

    /**
     * @param ProductInterface $product
     * @return string|null
     */
    public function getValue(ProductInterface $product): ?string
    {
        if (!empty($product->getData(self::TOTAL_BOUGHT))) {
            return (string) $product->getData(self::TOTAL_BOUGHT);
        }
        return null;
    }

    /**
     * @param ProductModel $product
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMessage(ProductModel $product): ?string
    {
        $qty = 0;
        $collection = $this->grouped->getAssociatedProducts($product);

        foreach ($collection as $item) {
            $qty += $this->getStockItem($item->getId());
        }

        if ($qty > 0 && $qty < 10) {
            return __("ALMOST GONE - only %1 remaining!", $qty);
        } else if ($qty < 50) {
            return __('Limited Availability!');
        } else if ($this->getValue($product) > 100 && $qty > 0) {
            return __('IN HIGH DEMAND!');
        } else if ($this->getValue($product) > 25 && $qty > 0) {
            return __('Selling fast!');
        }
        return null;
    }

    /**
     * @param $productId
     * @return float
     */
    public function getStockItem($productId)
    {
        return $this->stockRegistry->getStockItem($productId)->getQty();
    }
    
    public function getsocialCuedeal($sku){
    try{
        $url = "https://public-api.wowcher.co.uk/v1/socialcue/deal/$sku";
        //if the method is get
        $this->_curl->get($url);
        //response will contain the output in form of JSON string
        $response = $this->_curl->getBody();
       $result = json_decode($response);
       $current_hour = date("G");
       //print_r($result);
       $lastHour = $result->lastHour;
       $lastSixHours = $result->lastSixHours;
       $lastTwelveHours = $result->lastTwelveHours;
       $lastTwentyFourHours = $result->lastTwentyFourHours;
       $lastPurchasedTime = $result->lastPurchasedTime;
       if($current_hour < 6 && $lastTwentyFourHours > 1)
       {
         $hrtext = __("others bought this deal in the last 24 hours!");
          return "$lastTwentyFourHours $hrtext";
       }else if($current_hour < 12 && $lastSixHours > 1){
        $hrtext = __("others have already bought this morning!");
        return "$lastSixHours $hrtext";
       }else if($current_hour < 8 && $lastTwelveHours > 1){
         $hrtext = __("others have already bought this deal today!");
        return "$lastTwelveHours $hrtext";
       }else if($lastTwentyFourHours > 1){
        $hrtext = __("others bought this deal in the last 24 hours!");
        return "$lastTwentyFourHours $hrtext";
       }
    }
    catch (\Exception $e) {
    $code = $this->_storeManager->getStore()->getCode();
    $result = "store code ".$code.' '.$e;
    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/socialcue.log');
	$logger = new \Zend\Log\Logger();
	$logger->addWriter($writer);
	$logger->info($result);
    }
    }
}
