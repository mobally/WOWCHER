<?php declare(strict_types=1);
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ProductImport\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use OnTap\ProductImport\Model\Source\ArraySourceFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Psr\Log\LoggerInterface;

class Importer
{
    /**
     * @var FeedDownloader
     */
    protected FeedDownloader $feedDownloader;
	protected $productRepository;
    /**
     * @var Mapper
     */
    protected Mapper $mapper;

    /**
     * @var \Magento\ImportExport\Model\Import
     */
    protected \Magento\ImportExport\Model\Import $importModel;

    /**
     * @var ArraySourceFactory
     */
    protected ArraySourceFactory $arraySourceFactory;

    /**
     * @var ProductResource
     */
    protected ProductResource $productResource;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var LoggerInterface
     */
    protected $logger;
	protected $productAction;

    /**
     * Converter constructor.
     * @param FeedDownloader $feedDownloader
     * @param Mapper $mapper
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param ArraySourceFactory $arraySourceFactory
     * @param ProductResource $productResource
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        FeedDownloader $feedDownloader,
        Mapper $mapper,
        \Magento\ImportExport\Model\Import $importModel,
        ArraySourceFactory $arraySourceFactory,
        ProductResource $productResource,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        \Rvs\ExpiryProduct\Model\Grouplist $Grouplist,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Catalog\Model\ResourceModel\Product\Action $productAction
    ) {
        $this->feedDownloader = $feedDownloader;
        $this->mapper = $mapper;
        $this->importModel = $importModel;
        $this->arraySourceFactory = $arraySourceFactory;
        $this->productResource = $productResource;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->grouplist = $Grouplist;
		$this->productRepository = $productRepository;
		$this->productAction = $productAction;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return string[]
     */
    protected function getImportConfig(): array
    {
        return [
            'entity' => 'catalog_product',
            'validation_strategy' => 'validation-stop-on-errors',
            'allowed_error_count' => '10',
            'behavior' => 'append',
            '_import_field_separator' => ',',
            '_import_multiple_value_separator' => ',',
            '_import_empty_attribute_value_constant' => '__EMPTY__VALUE__'
        ];
    }

    /**
     * @param string $url
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromUrl(string $url,$input): void
    {
        $this->logger->debug(sprintf('Fetching data from: %s', $url));

        $data = $this->feedDownloader->fetchData($url,$input);

        if (empty($data)) {
            throw new \Exception('API returned empty response');
        }

        $serializer = new \Magento\Framework\Serialize\Serializer\Json();
        $data = $serializer->unserialize($data);

        if (empty($data)) {
            throw new \Exception('No deals returned from the API');
        }

        if (!isset($this->collection)) {
            $collection = $this->collectionFactory->create();
            $collection
                ->addAttributeToSelect('status');
            $this->collection = $collection->load();
        }

        $product = $this->collection->getItemByColumnValue('sku', $data['id']);
        if (!empty($product)) {
            $data['is_new'] = false;
            $data['product_online'] = $product->getStatus() == '2' ? '0' : '1';
        } else {
            $data['is_new'] = true;
            $data['product_online'] = '0';
        }

        $newData = [];
        $this->mapper->map($data, $newData);

        $this->importModel->setData($this->getImportConfig());

        $source = $this->arraySourceFactory->create([
            'colNames' => Mapper\Product::getColumnNames(),
            'data' => $newData
        ]);

        $this->logger->debug(sprintf('Import source created, validating...'));
        $this->importModel->validateSource($source);

        $errorAggregator = $this->importModel->getErrorAggregator();
        $this->logger->debug(sprintf(
            'Checked rows: %s, checked entities: %s, invalid rows: %s, total errors: %s',
            $this->importModel->getProcessedRowsCount(),
            $this->importModel->getProcessedEntitiesCount(),
            $errorAggregator->getInvalidRowsCount(),
            $errorAggregator->getErrorsCount()
        ));

        $errors = $errorAggregator->getAllErrors();
        foreach ($errors as $error) {
            if ($error->getErrorLevel() == ProcessingError::ERROR_LEVEL_CRITICAL) {
                throw new \Exception($error->getErrorMessage());
            }
        }

        $this->logger->debug(sprintf('Validation complete. Importing...'));
        $this->importModel->importSource();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
     
     public function updateAll($input)
    {
        $data = $this->feedDownloader->fetchData('https://public-api.wowcher.co.uk/europe/deal/feed',$input);

        if (empty($data)) {
            throw new \Exception('API returned empty response');
        }
        $serializer = new \Magento\Framework\Serialize\Serializer\Json();
        $data = $serializer->unserialize($data);
        
	$result = array();
	foreach ($data as $deal) {
	$result[] = $deal['id'];
	}
	
	$magento_array = $this->grouplist->getGroupList();
	
	$data = array_intersect($result,$magento_array);
	//print_r($data);
	$array_prepare = array();
	   foreach ($data as $deal) {
            $url = sprintf(
                //'https://deal-eu09.devwowcher.co.uk/europe/deal/%s',
                'https://public-api.wowcher.co.uk/europe/deal/%s',
                $deal
            );
echo nl2br($url);
            $this->logger->debug(sprintf('Fetching data from: %s', $url));

            $data = $this->feedDownloader->fetchData($url,$input);

            $data = $serializer->unserialize($data);
			$array_prepare[] = array("id" => $data['id'],"closingDate" => $data['closingDate'],"expiryDate" => $data['expiryDate'],"status" => $data['status']);
		}
		$this->updateProductdata($array_prepare);
		
	}
     
	 public function updateProductdata($array_prepare)
    {
		$storeIds = array(0,1,2,3,4);
		$current_date = date("Y-m-d h:i:sa");
		$strTotime = strTotime($current_date).'000';		
        foreach($array_prepare as $val)
		{
		$closing_date = $val['closingDate'];
		if($closing_date > $strTotime){
	    $sku = $val['id'];
        $product_id = $this->getProductData($sku);
        $updateAttributes['closingDate'] = $closing_date;
		$updateAttributes['is_expiry'] = 0;
		$updateAttributes['status'] = 1;
          foreach ($storeIds as $storeId) {
	    $this->productAction->updateAttributes([$product_id], $updateAttributes, $storeId); 
	}
		}
	}
	}
	 
	 public function getProductData($sku)
    {
        if ($this->productRepository->get($sku)) 
        {
            $product = $this->productRepository->get($sku);
            $id = $product->getEntityId();
            return $id;
        }
    }

	 
     
    public function importAll($input): void
    {
      //'https://feed-eu09.devwowcher.co.uk/europe/deal/feed'
        $data = $this->feedDownloader->fetchData('https://public-api.wowcher.co.uk/europe/deal/feed',$input);

        if (empty($data)) {
            throw new \Exception('API returned empty response');
        }


	//$data = '[{"id":16047407},{"id":15411422},{"id":15778376},{"id":16047435},{"id":16179034}]';


        $serializer = new \Magento\Framework\Serialize\Serializer\Json();
        $data = $serializer->unserialize($data);
        
	$result = array();
	foreach ($data as $deal) {
	$result[] = $deal['id'];
	}
	$magento_array = $this->grouplist->getGroupList();
	$data = array_diff($result,$magento_array);
	$count_deals = count($data);
	if($count_deals > 0){
	echo $count_deals.' New deals start to import';
	}
//exit;
        if (empty($data)) {
            throw new \Exception('No deals returned from the API');
        }

        $this->logger->debug(sprintf('We have %s deal to import', count($data)));

        if (!isset($this->collection)) {
            $collection = $this->collectionFactory->create();
            $collection
                ->addAttributeToSelect('status');
            $this->collection = $collection->load();
        }

        $this->importModel->setData(array_merge($this->getImportConfig(), [
            'behavior' => 'append'
        ]));

        $newData = [];
       foreach ($data as $deal) {
            $url = sprintf(
                //'https://deal-eu09.devwowcher.co.uk/europe/deal/%s',
                'https://public-api.wowcher.co.uk/europe/deal/%s',
                $deal
            );

            $this->logger->debug(sprintf('Fetching data from: %s', $url));

            $data = $this->feedDownloader->fetchData($url,$input);

            $data = $serializer->unserialize($data);
            $product = $this->collection->getItemByColumnValue('sku', $data['id']);
            if (!empty($product)) {
                $data['is_new'] = false;
                $data['product_online'] = $product->getStatus() == '2' ? '0' : '1';
            } else {
                $data['is_new'] = true;
                $data['product_online'] = '0';
            }
            $this->mapper->map($data, $newData);
        }

        $source = $this->arraySourceFactory->create([
            'colNames' => Mapper\Product::getColumnNames(),
            'data' => $newData
        ]);

        $this->logger->debug(sprintf('Import source created, validating...'));
        $this->importModel->validateSource($source);

        $errorAggregator = $this->importModel->getErrorAggregator();
        $this->logger->debug(sprintf(
            'Checked rows: %s, checked entities: %s, invalid rows: %s, total errors: %s',
            $this->importModel->getProcessedRowsCount(),
            $this->importModel->getProcessedEntitiesCount(),
            $errorAggregator->getInvalidRowsCount(),
            $errorAggregator->getErrorsCount()
        ));

        $errors = $errorAggregator->getAllErrors();
        foreach ($errors as $error) {
            if ($error->getErrorLevel() == ProcessingError::ERROR_LEVEL_CRITICAL) {
                throw new \Exception($error->getErrorMessage());
            }
            $this->logger->warning($error->getErrorMessage(), ['error' => $error]);
        }


        $this->logger->debug(sprintf('Validation complete. Importing...'));
        $this->importModel->importSource();
    }
}
