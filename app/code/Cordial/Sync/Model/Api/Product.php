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

class Product extends Client
{

    const SYNC_ATTR = 'cordial_sync';

    protected $attrColor = 'color';
    protected $attrSize = 'size';
    protected $attrUpc = 'upc';
    protected $attrManufacturer = 'manufacturer';

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\Eav\Model\Entity
     *
     */
    protected $entity;

    /**
     * @var \Magento\Catalog\Helper\Image
     *
     */
    protected $imageHelper;

    /*
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $attrOptionCollection;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /*
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributesCollection;

    public $extraFields = ['color' => 'color', 'size' => 'size', 'upc' => 'upc', 'manufacturer' => 'manufacturer'];


    /**
     * @param \Cordial\Sync\Helper\Data $helper
     * @param \Cordial\Sync\Model\Log $log
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributesCollection
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Cordial\Sync\Helper\Data $helper,
        \Cordial\Sync\Model\Log $log,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Eav\Model\Entity $entity,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributesCollection,
        \Psr\Log\LoggerInterface $logger,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
    
        $this->helper = $helper;
        $this->log = $log;
        $this->jsonHelper = $jsonHelper;
        $this->productModel = $productModel;
        $this->productRepository = $productRepository;
        $this->entity = $entity;
        $this->imageHelper = $imageHelper;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->attributesCollection = $attributesCollection;
        $this->logger = $logger;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Get Product EntityType
     */
    public function _getEntityTypeId()
    {
        return $this->entity->setType('catalog_product')->getTypeId();
    }

    public function update($product)
    {
        return $this->create($product);
    }

    /**
     * Create product
     *
     * @param int|\Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function create($product)
    {
        $storeId = $this->storeId;
        if (is_object($product)) {
            $productId = $product->getId();
            $product->setStoreId($this->storeId);
        } else {
            $productId = $product;
            $product = $this->productRepository->getById($productId, false, $storeId);
        }

        /* @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        if ($stockItem) {
            //throw new LocalizedException(__('The stock item for Product is not valid.'));
            $qty = $stockItem->getQty();
            $isInStock = $stockItem->getIsInStock();
        } else {
            $qty = 0;
            $isInStock = false;
        }

        $sync = $product->getCustomAttribute(Config::ATTR_CODE);
        if (!is_null($sync)) {
            if (!$sync->getValue()) {
                return false;
            }
        }

        $category = $this->helper->getCategory($product, $storeId);
        $images = $this->getProductImages($product, $storeId);
        if ($product->getData('tax_class_id') != '0') {
            $taxable = true;
        } else {
            $taxable = false;
        }
        $status = true;
        if ($product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
            $status = false;
        }

        $productUrl = $product->getUrlModel()->getUrl($product);
        if (is_null($productUrl)) {
            $productUrl = '';
        }
        $productUrl = preg_replace('/\?.*/', '', $productUrl);
        $extraFieldsValue = $this->getExtraFields($product, $storeId);
        $attr = [];
        if (!empty($extraFieldsValue['color'])) {
            $attr['color'] = $extraFieldsValue['color'];
        }
        if (!empty($extraFieldsValue['size'])) {
            $attr['size'] = $extraFieldsValue['size'];
        }
        $price = $product->getFinalPrice();
        $data = [
            'productID' => $productId,
            'productName' => $product->getName(),
            'variants' => [
                [
                    'sku' => $product->getSku(),
                    'attr' => $attr,
                    'qty' => (int)$qty
                ]],
            'productType' => $this->getProductType($product->getTypeId()),
            'price' => $price,
            'category' => $category,
            'images' => $images,
            'manufacturerName' => $extraFieldsValue['manufacturer'],
            'inStock' => (bool)$isInStock,
            'taxable' => $taxable,
            'enabled' => $status,
            'url' => $productUrl
        ];
        if (!empty($extraFieldsValue['upc'])) {
            $data['UPCCode'] = (string)$extraFieldsValue['upc'];
        }

        $path = "products";
        return $result = $this->_request('POST', $path, $data);
    }

    /**
     * Get Image Url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductImages($product)
    {
        $mediaGallery = $product->getMediaGalleryImages();
        $images = [];
        if ($mediaGallery instanceof \Magento\Framework\Data\Collection) {
            foreach ($mediaGallery as $image) {
                $images[] = $this->imageHelper->init($product, 'product_page_image_medium_no_frame')
                    ->setImageFile($image->getFile())
                    ->getUrl();
            }
        }

        return $images;
    }

    /**
     *
     * get Product Extra values
     *
     * @param array $extraFields
     * @param Mage_Catalog_Model_Product $product
     * @param $storeId
     * @return array
     */
    public function getExtraFields($product, $storeId = null)
    {
        $extraFieldsValue = [];
        if (!is_null($this->extraFields)) {

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributes */
            $attributes = $this->attributesCollection->create()
                ->addFieldToFilter('attribute_code', ['in' => array_values($this->extraFields)])
                ->load();
            foreach ($this->extraFields as $key => $extraField) {
                foreach ($attributes as $attribute) {
                    if ($attribute->getAttributeCode() == $extraField) {
                        if ($attribute->getFrontendInput() == 'multiselect' || $attribute->getFrontendInput() == 'select') {
                            $getExtraFieldValue = [];
                            $extraOptionIds = $product->getData($extraField);
                            $extraOptionIds = explode(',', $extraOptionIds);
                            $extraOptions = $this->_attrOptionCollectionFactory->create()
                                ->setAttributeFilter($attribute->getAttributeId())
                                ->load();
                            $extraOptions = $extraOptions->toArray();
                            if ($extraOptions['totalRecords']) {
                                foreach ($extraOptions['items'] as $extraOption) {
                                    if (in_array($extraOption['option_id'], $extraOptionIds)) {
                                        if (isset($extraOption['value'])) {
                                            $getExtraFieldValue[] = $extraOption['value'];
                                        }
                                    }
                                }
                            }
                            if (!empty($getExtraFieldValue)) {
                                $extraFieldsValue[$key] = implode(',', $getExtraFieldValue);
                            } else {
                                $extraFieldsValue[$key] = "";
                            }
                        } else {
                            if (!is_null($product->getData($extraField))) {
                                $extraFieldsValue[$key] = $product->getData($extraField);
                            } else {
                                $extraFieldsValue[$key] = '';
                            }
                        }
                        $found = true;
                        break;
                    }
                }
            }
        }

        return $extraFieldsValue;
    }

    /**
     * Delete product
     *
     * @param int|string $product
     * @return boolean
     */
    public function delete($product)
    {
        if (is_object($product)) {
            $productId = $product->getId();
        } else {
            $productId = $product;
        }

        $path = "products/$productId";
        $storeId = $this->storeId;

        $dateTime = new \DateTime();
        $createdAt = $dateTime->format(\Cordial\Sync\Model\Api\Config::DATE_FORMAT);
        $data = [];
        $data['properties']['magentoDeleted'] = $createdAt;
        $deleted = $this->_request('PUT', $path, $data);

        if (!$deleted) {
            return false;
        }

        return true;
    }

    /**
     *
     * Return Cordial product type
     *
     * @param string $productType
     * @return string
     */
    public function getProductType($productType)
    {
        switch ($productType) {
            case 'simple':
            case 'configurable':
            case 'bundle':
            case 'grouped':
                return 'physical';
                break;
            case 'virtual':
            case 'downloadable':
                return 'digital';
                break;
        }
        return '';
    }
}
