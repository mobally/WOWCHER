<?php
namespace Rvs\ProductImportJson\Model;
use \Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\ManagerInterface;

class ProductProcessBatch
{
	const MIDAS_FILE_CODE = "/mnt/nfs/wowcher_repo/productimport/";
    const DEFAULT_GROUPID = 110;

    protected $groupFactory;
    protected $optionSaver;
    protected $_productOptions;
    protected $customOptionFactory;
    
    protected $productFactory;
    protected $stockRegistry;
    private $_fileDriver;
    protected $_productCollectionFactory;
    protected $_storeManager;
    protected $eventManager;
	protected $scopeConfig;
	protected $storeRepository;
	protected $productModel;

   public function __construct(
       //StockRegistryInterface $stockRegistry,
       \Magento\Backend\Block\Template\Context $context,
	   \Magento\Framework\Filesystem\Driver\File $fileDriver,
       \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
       \Magento\Catalog\Model\ProductFactory $productFactory,
       \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
       \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
       \Magento\Store\Model\StoreManagerInterface $storeManager,
       \Magento\Catalog\Model\Product\Option $productOptions,
       CustomOptionFactory $customOptionFactory = null,
       ManagerInterface $eventManager,
	   \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	   \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
	   \Magento\Catalog\Model\Product $productModel,
	   array $data = []
   ){      
        $this->_fileDriver = $fileDriver;
        $this->_productCollectionFactory = $productCollectionFactory;   
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->_storeManager = $storeManager;
        $this->_productOptions = $productOptions;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->customOptionFactory = $customOptionFactory ?: $objectManager->get(CustomOptionFactory::class);
        $this->eventManager = $eventManager;
	   	$this->scopeConfig = $scopeConfig;
		$this->storeRepository= $storeRepository;
		$this->productModel = $productModel;
   } 
	
	public function getMidasSystemInfo($code)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this
            ->scopeConfig
            ->getValue($code, $storeScope);

    }
    
    public function getFileExists()
    {	
		$code = self::MIDAS_FILE_CODE;
		//$midas_pat = $this->getMidasSystemInfo($code);
		$dir = $code;
		
		$datas = scandir($dir);
		
		$file_path = array();

		foreach($datas as $files_extension)
		{
			$extension = substr($files_extension, strrpos($files_extension, '.')); // Gets the File Extension
			
			if($extension == ".json"){
				/*if (strpos($files_extension, 'products.xml') !== false) {
					if($files_extension)
						$file_path[] = $files_extension;					
				}*/
				$file_path[] = $files_extension;	
			}
		}
		
		$file_path_list = array_slice($file_path, 0, 20, true);
		if($file_path_list){
			return $file_path_list;    
		}
		else{
			return $file_path_list = '';
		}        
    }    
    
    public function getWebsites()
    {
		$storeManagerDataList = $this->_storeManager->getWebsites();
		$options = array();
		foreach ($storeManagerDataList as $key => $value) {
			$code = $value['code'];

			if($code != 'amazon_marketplace'):
				$options[] = $value['value'];
			endif;
		}		
		return $options;
    }
    public function getStoreID($code){
		$store = $this->storeRepository->get($code);
		return $store->getId(); // this is the store ID
		
	}
	public function moveUploadFile($filename)
    {
		if($filename){
		$code = self::MIDAS_FILE_CODE;
					
		$xml_file_path = $code.$filename; 
		$xml_file_path_upload = $code."movedfiles/".$filename;
		
		copy($xml_file_path, $xml_file_path_upload);
		unlink($xml_file_path);
		}
    }
    
    public function execute()
    {	
		
		$is_fileValid = $this->getFileExists();
		if($is_fileValid){
			foreach($is_fileValid as $filepath){
				$filename = $filepath;
				$code = self::MIDAS_FILE_CODE;
				$file_path = $code.$filename; 
				
				if ($this->_fileDriver->isExists($file_path)) {					
					$splitfilename = explode("(",$filename);
					
					$skusplit =  explode("_",$splitfilename[0]);
					$sku = $skusplit[1];
					
					$storecodeSplit = explode(")",$splitfilename[1]);
					$storecodeMain = $storecodeSplit[0];
					if($storecodeMain == 'fr'){
						$storecode = 'be_FR';
					}else{
						$storecode = $storecodeMain;
					}
					$storeID = $this->getStoreID($storecode);
					
					//$filePath = simplexml_load_file($file_path);
					$json = file_get_contents($file_path);				
					$Array_prepare = json_decode($json,TRUE);
					$productSku = $sku;
						if( $this->productFactory->create()->getIdBySku($productSku)){	
							$this->UpdateProduct($Array_prepare,$productSku,$storeID);
						}else{
							$this->NewproductCreate($Array_prepare,$productSku,$storeID);
						}			
					$this->moveUploadFile($filename);
				}
			}
		}

		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/error.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info("No file found in specific location");	
    }
        
      public function getProductCollection($producsku,$productskucode)
    {   
        $products = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('sku')
            ->addAttributeToFilter($producsku,$productskucode)
            ->load();
        
        return $products->getFirstItem();
    }
    
	public function UpdateProduct($stock_data_val,$sku,$storeID)
    {
		$skuMain = $sku;
		$name               = isset($stock_data_val['name']) ? $stock_data_val['name'] : '';
		$description        = isset($stock_data_val['description']) ? $stock_data_val['description'] : '';   
		$short_description  = isset($stock_data_val['short_description']) ? $stock_data_val['short_description'] : ''; 
		$deal_position      = isset($stock_data_val['deal_position']) ? $stock_data_val['deal_position'] : '';   		
		$business_id        = isset($stock_data_val['business_id']) ? $stock_data_val['business_id'] : '';   
		$business_image_url = isset($stock_data_val['business_image_url']) ? $stock_data_val['business_image_url'] : '';   
		$business_image_alt = isset($stock_data_val['business_image_alt']) ? $stock_data_val['business_image_alt'] : '';  
		$web_address        = isset($stock_data_val['web_address']) ? $stock_data_val['web_address'] : '';   
		$total_bought       = isset($stock_data_val['total_bought']) ? $stock_data_val['total_bought'] : ''; 
		$highlights         = isset($stock_data_val['highlights']) ? $stock_data_val['highlights'] : ''; 
		$terms              = isset($stock_data_val['terms']) ? $stock_data_val['terms'] : ''; 
		$price_text         = isset($stock_data_val['price_text']) ? $stock_data_val['price_text'] : ''; 
		$productdisplay_row = isset($stock_data_val['productdisplay_row']) ? $stock_data_val['productdisplay_row'] : ''; 
		$productdisplay_column = isset($stock_data_val['productdisplay_column']) ? $stock_data_val['productdisplay_column'] : ''; 
		$storeId  = $storeID;
		
		$productSku = $this->productFactory->create()->getIdBySku($skuMain);
		$product = $this->productFactory->create()->load($productSku);
		$product->addAttributeUpdate('name', $name, $storeId);
		$product->addAttributeUpdate('description', $description, $storeId);
		$product->addAttributeUpdate('short_description', $short_description, $storeId);
		$product->addAttributeUpdate('deal_position', $deal_position, $storeId);
		$product->addAttributeUpdate('highlights', $highlights, $storeId);
		$product->addAttributeUpdate('terms', $terms, $storeId);
		$product->addAttributeUpdate('price_text', $price_text, $storeId);
		$product->addAttributeUpdate('productdisplay_row', $productdisplay_row, $storeId);
		$product->addAttributeUpdate('productdisplay_column', $productdisplay_column, $storeId);
		$product->addAttributeUpdate('total_bought', $total_bought, $storeId);
		$product->addAttributeUpdate('business_id', $business_id, $storeId);
		$product->addAttributeUpdate('business_image_alt', $business_image_alt, $storeId);
		$product->addAttributeUpdate('business_image_url', $business_image_url, $storeId);		
		$product->addAttributeUpdate('web_address', $web_address, $storeId);
    }
    public function NewproductCreate($stock_data_val,$sku,$storeID)
    {
		$name               = isset($stock_data_val['name']) ? $stock_data_val['name'] : '';
		$description        = isset($stock_data_val['description']) ? $stock_data_val['description'] : '';   
		$short_description  = isset($stock_data_val['short_description']) ? $stock_data_val['short_description'] : ''; 
		$deal_position      = isset($stock_data_val['deal_position']) ? $stock_data_val['deal_position'] : '';   		
		$business_id        = isset($stock_data_val['business_id']) ? $stock_data_val['business_id'] : '';   
		$business_image_url = isset($stock_data_val['business_image_url']) ? $stock_data_val['business_image_url'] : '';   
		$business_image_alt = isset($stock_data_val['business_image_alt']) ? $stock_data_val['business_image_alt'] : '';  
		$web_address        = isset($stock_data_val['web_address']) ? $stock_data_val['web_address'] : '';   
		$total_bought       = isset($stock_data_val['total_bought']) ? $stock_data_val['total_bought'] : ''; 
		$highlights         = isset($stock_data_val['highlights']) ? $stock_data_val['highlights'] : ''; 
		$terms              = isset($stock_data_val['terms']) ? $stock_data_val['terms'] : ''; 
		$price_text         = isset($stock_data_val['price_text']) ? $stock_data_val['price_text'] : ''; 
		$productdisplay_row = isset($stock_data_val['productdisplay_row']) ? $stock_data_val['productdisplay_row'] : ''; 
		$productdisplay_column = isset($stock_data_val['productdisplay_column']) ? $stock_data_val['productdisplay_column'] : '';  
		$skuMain = $sku;
		$storeId = $storeID;
		$attributeSetId     = 4;

       
		$name_prefix = str_replace(" ", "-", $name); 
		$url = $name_prefix;
        $product = $this->productFactory->create();
        $product->setData('_edit_mode', true);
        $product->setStoreId($storeID);
        $product->setAttributeSetId($attributeSetId);
        $product->setSku($skuMain);
        $product->setName($name);
        $product->setDescription($description);
		$product->setShortDescription($short_description);
		$product->setDealPosition($deal_position);
        $product->setAttributeSetId(4);
        $product->setVisibility(4);
        $product->setWeight(1);        
        $product->setWebsiteIds([$storeId]);        
        $product->setExcludeFromStock(0);
        $product->setStatus(2);
        $product->setBusinessImageUrl($business_image_url);
        $product->setBusinessImageAlt($business_image_alt);
        $product->setBusinessId($business_id);
		$product->setWebAddress($web_address);
		$product->setTotalBought($total_bought);
		$product->setHighlights($highlights);
		$product->setTerms($terms);
		$product->setPriceText($price_text);
		$product->setProductdisplayRow($productdisplay_row);
		$product->setProductdisplayColumn($productdisplay_column);
        $product->setTaxClassId(2);
		$product->setUrlKey($url);
       
       
        $product->save();
		/*if($product_type == 'grouped'){
		 $group_product_id = $product->getId();
		 $group_product_sku = $product->getSku();
		$associated = [];
		$position = 0;
		$str_arr = explode (",", $associated_skus); 
		foreach($str_arr as $associatedSkus)
		{	
			$position++;
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$productLink = $objectManager->create('\Magento\Catalog\Api\Data\ProductLinkInterface');
			$productLink->setSku($group_product_sku)
				->setLinkType('associated') // Set Link Type
				->setLinkedProductSku($associatedSkus) // Set Link Product SKU
				->setLinkedProductType('simple') // Set Link Product Type
				->setPosition($position) // Set Position
				->getExtensionAttributes()
				->setQty(1);
			// Set Associated Product Default QTY
			$associated[] = $productLink;
		}
		
		$product->setProductLinks($associated);
		$product->save();
		
		}*/
    }

    public function getDefaultGroupOption($product){
        $groupId  = self::DEFAULT_GROUPID;
        $group    = $this->groupFactory->create()->load($groupId);
        
        $modProductOptions = [];
        $this->optionSaver->setIsTemplateSave(false);
        $modProductOptions = $this->optionSaver->addNewOptionProcess($modProductOptions, $group);
        
        $customOptions = [];
        foreach ($modProductOptions as $customOptionData) {
            $customOption = $this->customOptionFactory->create(['data' => $customOptionData]);
            $customOption->setProductSku($product->getSku());
            $customOptions[] = $customOption;
        }

        return $customOptions;
    }

    public function setDefaultGroupOptionResource($product){
        $groupId  = self::DEFAULT_GROUPID;
        $group    = $this->groupFactory->create();
        $resource = $group->getResource();        
        $resource->addProductRelation($groupId, $product->getId());
    }
}
