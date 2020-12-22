<?php

namespace Freshrelevance\Digitaldatalayer\Helper;

use Exception;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\GroupedProduct\Pricing\Price\FinalPrice;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;

class Product extends AbstractHelper
{
    const STOCK_EXPOSURE_ENABLED = 2;
    private $store;
    private $config;
    private $configurableModel;
    private $productModel;
    private $stockInterface;
    private $categoryModel;
    /**
     * @var Category
     */
    protected $catHelper;
    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;
    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * Product constructor.
     * @param Category $catHelper
     * @param StoreManagerInterface $store
     * @param Config $config
     * @param Configurable $configurableModel
     * @param ProductRepository $productModel
     * @param StockStateInterface $stockInterface
     * @param \Magento\Catalog\Model\Category $categoryModel
     * @param ReviewFactory $reviewFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Category $catHelper,
        StoreManagerInterface $store,
        Config $config,
        Configurable $configurableModel,
        ProductRepository $productModel,
        StockStateInterface $stockInterface,
        \Magento\Catalog\Model\Category $categoryModel,
        ReviewFactory $reviewFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->store                 = $store->getStore();
        $this->config                = $config;
        $this->configurableModel     = $configurableModel;
        $this->productModel          = $productModel;
        $this->stockInterface        = $stockInterface;
        $this->categoryModel         = $categoryModel;
        $this->reviewFactory         = $reviewFactory;
        $this->catHelper             = $catHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
    }

    public function getBundleSelectedLinkedProducts($item)
    {
        $linkedProducts = [];
        $product        = $item->getProduct();

        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds       = $optionsQuoteItemOption ? json_decode($optionsQuoteItemOption->getValue()) : [];
        if ($bundleOptionsIds) {
            /** @var Collection $optionsCollection */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $bundleSelectionIds = json_decode($selectionsQuoteItemOption->getValue());

            if (!empty($bundleSelectionIds)) {
                $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);

                $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
                foreach ($bundleOptions as $bundleOption) {
                    if ($bundleOption->getSelections()) {
                        $bundleSelections = $bundleOption->getSelections();
                        foreach ($bundleSelections as $bundleSelection) {
                            $linkedProducts[] = $this->getProductData($bundleSelection);
                        }
                    }
                }
            }
        }
        return $linkedProducts;
    }

    public function getBundleLinkedProducts($product)
    {
        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $product->getTypeInstance(true)->getOptionsIds($product),
            $product
        );
        $linkedProducts      = [];
        foreach ($selectionCollection as $product) {
            $linkedProducts[] = $this->getProductData($product);
        }
        return $linkedProducts;
    }

    public function getConfigurableLinkedProducts($product)
    {
        $products     = $this->configurableModel->getUsedProducts($product);
        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = $this->getProductData($product);
        }
        return $productsData;
    }

    public function getConfigurableSelectedLinkedProducts($item)
    {
        $productModel  = $this->productModel;
        $simpleProduct = $productModel->get($item->getSku());
        return $this->getProductData($simpleProduct);
    }

    public function getGroupedLinkedProducts($product)
    {
        $products     = $product->getTypeInstance()->getAssociatedProducts($product);
        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = $this->getProductData($product);
        }
        return $productsData;
    }

    public function getGroupedSelectedLinkedProducts($item)
    {
        $productModel  = $this->productModel;
        $simpleProduct = $productModel->get($item->getSku());
        return $this->getProductData($simpleProduct);
    }

    public function getProductInfo(\Magento\Catalog\Model\Product $product)
    {
        $productInfo                = [];
        $productInfo['productID']   = $product->getEntityId();
        $productInfo['productName'] = $product->getName();
        $productInfo['sku']         = $product->getSku();
        $productInfo['description'] = strip_tags($product->getData('description'));
        $productInfo['productURL']  = $product->setStoreId($this->store->getId())->getUrlInStore();
        if ($product->getImage() && $product->getImage() !== "no_selection") {
            $productInfo['productImage'] = $this->store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
                'catalog/product' . $product->getImage();
        }
        if ($product->getThumbnail() && $product->getImage() !== "no_selection") {
            $productInfo['productThumbnail'] = $this->store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .
                'catalog/product' . $product->getThumbnail();
        }

        foreach ($this->config->getEnabledProductAttributes() as $attr) {
            // Protect against blank and none in attr
            if ($attr && $attr !== 'none' && $product->getData($attr)) {
                $productInfo['attributes'][$attr] = $product->getData($attr);
            }
            if ($attr && $attr == 'all' && $product->getData()) {
                $productInfo['attributes'] = $product->getData();
                // Break to make sure we don't double populate any fields
                break;
            }
        }
        if ($this->config->getEnabledStockExposure() != 0) {
            $productInfo['stock'] = $this->getProductStockInfo($product, $this->config->getEnabledStockExposure());
        } else {
            // Remove stock exposure from attributes if it was exposed
            if (in_array('attributes', $productInfo) && in_array(
                    'quantity_and_stock_status',
                    $productInfo['attributes']
                )) {
                unset($productInfo['attributes']['quantity_and_stock_status']);
            }
        }
        // Fetch Product Rating and Rating Count
        if ($this->config->getEnabledRatingExposure() != 0) {
            $prodRating = $this->getRatingSummary($product);
            if ($prodRating) {
                $productInfo['rating']      = $prodRating[0];
                $productInfo['ratingCount'] = $prodRating[1];
            } else {
                $productInfo['rating'] = null;
            }
        }
        return $productInfo;
    }

    public function getSelectionQty(\Magento\Catalog\Model\Product $product, $selectionId)
    {
        $selectionQty = $product->getCustomOption('selection_qty_' . $selectionId);
        if ($selectionQty) {
            return $selectionQty->getValue();
        }
        return 0;
    }

    public function getRatingSummary($product)
    {
        $rating      = null;
        $ratingCount = null;

        try {
            $this->reviewFactory->create()->getEntitySummary($product, $this->store->getId());
            $rating      = $product->getRatingSummary()->getRatingSummary();
            $ratingCount = $this->reviewFactory->create()->getTotalReviews($product->getEntityId(), false);
        } catch (Exception $e) {
        }

        return [$rating, $ratingCount];
    }

    public function getProductStockInfo($product, $config)
    {
        $info       = null;
        $StockState = $this->stockInterface;
        if ($config == self::STOCK_EXPOSURE_ENABLED) {
            $info = $StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
        } else {
            $info = $product->isInStock() ? 'in stock' : 'out of stock';
        }

        return $info;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @throws LocalizedException
     */
    public function getProductData($product)
    {
        $price       = [];
        $categories  = [];
        $productData = [];

        if ($product) {
            $categories                 = $this->catHelper->getProductCategories($product);
            $productData['productInfo'] = $this->getProductInfo($product);

            /** @var Base $priceObj */
            if ($priceObj = $product->getPriceInfo()) {
                if ($product->getPrice() !== null) {
                    list($basePrice, $priceWithTax) = $this->getProductPriceData($priceObj);
                } else {
                    // Likely a Configurable or Bundle Product, use minimal price
                    list($basePrice, $priceWithTax) = $this->getProductPriceData($priceObj, true);
                }
                $price['basePrice']    = $basePrice;
                $price['priceWithTax'] = $priceWithTax;
            }
            if ($product->isSaleable()) {
                $price['regularPrice'] = $priceObj->getPrice(RegularPrice::PRICE_CODE)->getAmount()->getValue();
            }
        }

        $price['currency']       = $this->store->getCurrentCurrencyCode();
        $productData['category'] = $categories;
        $productData['price']    = $price;

        return $productData;
    }

    /**
     * Get Data Rich products by ID
     *
     * @param array $ids
     * @param bool $sortByIdDescending - return in id descending order or not.
     * @return ProductInterface[]
     */
    public function getProductsByIDS(array $ids, $sortByIdDescending = true)
    {
        $products  = [];
        $filters[] = $this->filterBuilder->setField('entity_id')
                                         ->setConditionType('in')
                                         ->setValue($ids)
                                         ->create();

        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters);

        if ($sortByIdDescending) {
            $sortByIdDescending = $this->sortOrderBuilder->setField('entity_id')
                                                         ->setDescendingDirection()
                                                         ->create();

            $searchCriteria->addSortOrder($sortByIdDescending);
        }

        $searchResults = $this->productModel->getList($searchCriteria->create());

        foreach ($searchResults->getItems() as $product) {
            $products[$product->getId()] = $product;
        }
        return $products;
    }

    /**
     * Return the base price and price with tax
     *
     * @param Base $priceObject - Product price object
     * @param bool $useFinalPrice - whether to use the final price object to get values
     * @return array
     */
    protected function getProductPriceData($priceObject, $useFinalPrice = false)
    {
        $basePrice    = null;
        $priceWithTax = null;

        if (!$finalPriceObject = $priceObject->getPrice(FinalPrice::PRICE_CODE)) {
            return [$basePrice, $priceWithTax];
        }

        $priceObject = $useFinalPrice ? $finalPriceObject : $priceObject;

        try {
            if ($useFinalPrice) {
                $basePrice    = $this->getMinimalPrice($priceObject);
                $priceWithTax = $priceObject->getAmount() ? $priceObject->getAmount()->getValue() : null;
            } else {
                $base         = $priceObject->getPrice(BasePrice::PRICE_CODE);
                $basePrice    = $base->getAmount() ? $base->getAmount()->getValue() : null;
                if (!$basePrice) {
                    // Getting base price failed, try minimal price (potential configurable bug fix)
                    $basePrice = $this->getMinimalPrice($finalPriceObject);
                }
                $priceWithTax = $finalPriceObject->getAmount() ? $finalPriceObject->getAmount()->getValue() : null;
            }
        } catch (Exception $exception) {
            $this->_logger->debug("Issue fetching price information for product:\n {$exception->getMessage()}");
        }

        return [$basePrice, $priceWithTax];
    }


    /**
     * Get the Final price depending on the product type.
     * @param $finalPriceObject
     * @return float|null
     */
    protected function getMinimalPrice($finalPriceObject)
    {
        try {
            if (get_class($finalPriceObject) == FinalPrice::class) {
                /** @var FinalPrice $finalPriceObject */
                if ($minProduct = $finalPriceObject->getMinProduct()) {
                    return $minProduct->getPriceInfo() ? $minProduct->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue() : 0.00;
                }
                return 0.00;
            }

            return $finalPriceObject->getMinimalPrice() ? $finalPriceObject->getMinimalPrice()->getValue('amount') : null;
        } catch (Exception $e){
            // Return 0 in case something goes wrong
            return 0.00;
        }
    }
}
