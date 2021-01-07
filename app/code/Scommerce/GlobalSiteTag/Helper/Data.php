<?php
/**
 * Scommerce Global Tag Manager Data Helper
 *
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GlobalSiteTag\Helper;

use \Magento\CatalogInventory\Model\Stock\StockItemRepository;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Registry;
use \Magento\Catalog\Model\Product;
use Magento\Inventory\Model\StockRepository;
use \Magento\Quote\Model\Quote\Item as QuoteItem;
use \Magento\Catalog\Helper\Product as ProductHelper;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Stdlib\CookieManagerInterface;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\CatalogInventory\Model\Stock\Item;
use \Magento\Framework\Serialize\SerializerInterface;
use \Magento\Catalog\Api\CategoryRepositoryInterface;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\App\Request\Http;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as StockItem;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Admin configuration paths
     *
     */
    const XML_PATH_ENABLED = 'globalsitetag/general/active';
    const XML_PATH_LICENSE_KEY = 'globalsitetag/general/license_key';
    const XML_PATH_BASE = 'globalsitetag/general/base';
    const XML_PATH_ENHANCED_ECOMMERCE = 'globalsitetag/general/enhanced_ecommerce_enabled';
    const XML_PATH_ENABLE_DYNAMIC = 'globalsitetag/general/enable_dynamic';
    const XML_PATH_ACCOUNT_ID = 'globalsitetag/general/account_id';
    const XML_PATH_ACCOUNTS_ID = 'globalsitetag/general/accounts_id';
    const XML_PATH_PROMOTION_TRACKING = 'globalsitetag/general/promotion_tracking';
    const XML_PATH_ENHANCED_BRAND_DROPDOWN = 'globalsitetag/general/brand_dropdown';
    const XML_PATH_ENHANCED_BRAND_TEXT = 'globalsitetag/general/brand_text';
    const XML_PATH_OPTIMIZE_ID = 'globalsitetag/general/optimize_id';
    const XML_PATH_ENABLE_OPTIMIZE = 'globalsitetag/general/enable_optimize';
    const XML_PATH_ENABLE_LINKER = 'globalsitetag/general/enable_linker';
    const XML_PATH_DECORATE_FORMS = 'globalsitetag/general/decorate_forms';
    const XML_PATH_DOMAINS_TO_LINK = 'globalsitetag/general/domains_to_link';
    const XML_PATH_ENABLE_OTHER_SITES = 'globalsitetag/general/enable_other_sites';

    const XML_PATH_BACKEND_TRACKING_ENABLED = 'globalsitetag/backend_tracking/active';
    const XML_PATH_BACKEND_SOURCE           = 'globalsitetag/backend_tracking/source';
    const XML_PATH_BACKEND_MEDIUM           = 'globalsitetag/backend_tracking/medium';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var StockItem
     */
    protected $stockItemResource;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    protected $stockItemFactory;

    /**
     * Json Serializer
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Data constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ProductHelper $productHelper
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param ObjectManagerInterface $objectManager
     * @param StockItemRepository $stockItem
     * @param SerializerInterface $serializer
     * @param Http $request
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductHelper $productHelper,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        ObjectManagerInterface $objectManager,
        StockItemRepository $stockItem,
        SerializerInterface $serializer,
        StockItem $stockItemResource,
        StockItemInterfaceFactory $stockItemFactory,
        Http $request
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
        $this->productHelper = $productHelper;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->objectManager = $objectManager;
        $this->cookieManager = $cookieManager;
        $this->storeManager = $storeManager;
        $this->stockItemRepository = $stockItem;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->stockItemResource = $stockItemResource;
        $this->stockItemFactory = $stockItemFactory;
    }


    /**
     * Returns whether module is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_STORE
            ) && $this->isLicenseValid() && $this->getMainAccount();
    }

    /**
     * returns account id
     * @return string
     */
    public function getAccountId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ACCOUNT_ID,
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns whether linker forms is enabled or not
     * @return string
     */
    public function isLinkerEnable()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_LINKER,
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns whether decorate forms is enabled or not
     * @return string
     */
    public function isDecorateFormsEnable()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DECORATE_FORMS,
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns domains to link
     * @return array|bool
     */
    public function getDomainsToLink()
    {
        $config = $this->scopeConfig->getValue(
            self::XML_PATH_DOMAINS_TO_LINK,
            ScopeInterface::SCOPE_STORE);
        $domains = explode(',', $config);
        if (count($domains) > 0) {
            return $domains;
        }

        return false;
    }

    /**
     * Returns accounts id
     * @return array
     */
    public function getAccountsId()
    {
        $config = $this->scopeConfig->getValue(self::XML_PATH_ACCOUNTS_ID, ScopeInterface::SCOPE_STORE);
        if (isset($config)) {
            $decodedValue = $this->serializer->unserialize($config);
            return $decodedValue;
        }
        return array();
    }

    /**
     * Returns accounts id
     *
     * @return string|bool
     */
    public function getMainAccount()
    {
        $ids = $this->getAccountsId();
        foreach ($ids as $id) {
            if (isset($id['main_account']) && $id['main_account'] == '1') {
                return $id['account_id'];
            }
        }
        return false;
    }

    /**
     * Checks to see if the other site variable is enabled or not
     *
     * @return boolean
     */
    public function isOtherSiteEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_OTHER_SITES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns accounts id
     *
     * @return string|bool
     */
    public function getAdWordsAccountId()
    {
        $accounts = [];
        $ids = $this->getAccountsId();
        foreach ($ids as $id) {
            if ($id['account_type'] == 'adword') {
                $accounts[] = $id['account_id'];
            }
        }
        if (!empty($accounts)) {
            return implode(',', $accounts);
        }
        return false;
    }

    /**
     * Returns whether optimize is enabled or not
     * @return string
     */
    public function isOptimizeEnabled() {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_OPTIMIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns whether enhanced ecommerce is enabled or not
     * @return string
     */
    public function isEnhancedEcommerceEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENHANCED_ECOMMERCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Checks to see if the extension is enabled for advanced tagging in admin
     *
     * @return bool
     */
    public function isDynamicRemarketingEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_DYNAMIC,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns current store currency code
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Returns Optimize Id
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOptimizeId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_OPTIMIZE_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns whether base order data is enabled or not
     *
     * @return boolean
     */
    public function sendBaseData()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_BASE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * returns formatted produce price
     * @param Product $product
     * @return float
     */
    public function getProductPrice($product)
    {
        $price = 0;
        if ($this->productHelper->getFinalPrice($product) > 0) {
            $price = $this->productHelper->getFinalPrice($product);
        } elseif ($this->productHelper->getPrice($product) > 0) {
            $price = $this->productHelper->getPrice($product);
        }
        return number_format($price, 2);
    }

    /**
     * @param Product $product
     * @return bool|float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductQty($product)
    {
        if (!($product instanceof Product)) {
            return false;
        }

        /** @var Item $productStock */
        try {
            $productStock = $this->getStockItemByProductId($product->getId());
            if (!($productStock instanceof Item)) {
                return false;
            }

            $qty = $productStock->getQty();
        } catch (\Exception $e) {
            $qty = 0;
        }

        return $qty;
    }

    /**
     * @param string|int $id
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategory($id)
    {
        return $this->categoryRepository->get($id);
    }

    /**
     * Returns product category name
     *
     * @param Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCategoryName($product)
    {
        $cats = $product->getCategoryIds();
        $categoryId = array_pop($cats);
        return $categoryId ? $this->getCategory($categoryId)->getName() : '';
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderGoogleCategoryName($orderItem) {
        if ($catName = $orderItem->getGoogleCategory()) {
            return $catName;
        }

        $product = $orderItem->getProduct();
        return $this->getProductCategoryName($product);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteGoogleCategoryName($quoteItem) {
        if ($catName = $quoteItem->getGoogleCategory()) {
            return $catName;
        }

        return $this->getQuoteCategoryName($quoteItem);
    }

    /**
     * returns category name
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteCategoryName($quoteItem)
    {
        if ($catName = $quoteItem->getCategory()) {
            return $catName;
        }

        return $this->getProductCategoryName($quoteItem->getProduct());
    }

    /**
     * returns whether promotion tracking is enabled or not
     * @return boolean
     */
    public function isPromotionTrackingEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_PROMOTION_TRACKING,
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * returns attribute id of brand
     * @return string
     */
    public function getBrandDropdown()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENHANCED_BRAND_DROPDOWN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * returns brand static text
     * @return string
     */
    public function getBrandText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENHANCED_BRAND_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Product $product
     *
     * @param $itemProduct
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductVariant($product, $itemProduct)
    {
        if (!$this->isConfigurable($product)) {
            return '';
        }
        $result = array();
        $itemSKU = $itemProduct->getSku();
        $item = $this->productRepository->get($itemSKU);

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $type */
        $type = $product->getTypeInstance();
        $attributes = $type->getUsedProductAttributes($product);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($attributes as $attribute) {
            $result[] = $attribute->getFrontendLabel() . ': ' . $item->getAttributeText($attribute->getAttributeCode());
        }

        return implode(', ', $result);
    }

    /**
     * TODO returns product variant
     * @param Product $product
     * @return string
     */
    public function getVariant($product)
    {
        if (!$this->isConfigurable($product)) {
            return '';
        }
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $type */
        $type = $product->getTypeInstance();
        $data = $type->getConfigurableOptions($product);
        $variants = [];
        foreach ($data as $optionsData) {
            foreach ($optionsData as $option) {
                $variants[] = $option['option_title'];
            }
        }
        return implode(',', $variants);
    }

    /**
     * returns brand value using product or text
     * @param Product $product
     * @return int
     */
    public function getBrand($product)
    {
        if ($attribute = $this->getBrandDropdown()) {
            $data = $product->getAttributeText($attribute);
            if (is_array($data)) $data = end($data);
            if (strlen($data) == 0) {
                $data = $product->getData($attribute);
            }
            return $data;
        }
        return $this->getBrandText();
    }

    /**
     * Check if specified product is configurable
     *
     * @param Product $product
     * @return bool
     */
    public function isConfigurable($product)
    {
        return $product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    /**
     * returns license key administration configuration option
     *
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LICENSE_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getPageType()
    {
        $ecommPageType = '"other"';
        switch ($this->request->getFullActionName()) {
            case 'cms_index_index':
                $ecommPageType = 'home';
                break;
            case 'catalog_product_view':
                $ecommPageType = 'product';
                break;
            case 'catalog_category_view':
                $ecommPageType = 'category';
                break;
            case '‌catalogsearch_result_index':
                $ecommPageType = 'searchresults';
                break;
            case '‌checkout_cart_index':
                $ecommPageType = 'cart';
                break;
            case '‌checkout_index_index':
                $ecommPageType = 'cart';
                break;
        }

        return $ecommPageType;
    }

    /**
     * @return bool
     */
    public function backendTrackingEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_BACKEND_TRACKING_ENABLED,
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getBackendSource()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BACKEND_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getBackendMedium()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BACKEND_MEDIUM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $productId
     * @return StockItem|null
     */
    private function getStockItemByProductId($productId)
    {
        $item = $this->stockItemFactory->create();
        $this->stockItemResource
            ->loadByProductId($item, $productId,
                \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID);
        if ($item && $item->getId()) {
            return $item;
        }
        return null;
    }

    /**
     * returns whether license key is valid or not
     *
     * @return bool
     */
    public function isLicenseValid(){return true;$sku = strtolower(str_replace('\\Helper\\Data','',str_replace('Scommerce\\','',get_class($this))));return $this->_isLicenseValid($this->getLicenseKey(),$sku);}

    /**
     * returns whether license key is valid or not
     *
     * @return bool
     */
    private function _isLicenseValid($licensekey,$sku){$website = $this->getWebsite($_SERVER['HTTP_HOST']);$sku=$this->getSKU($sku);return password_verify($website.'_'.$sku, $licensekey);}

    /**
     * returns real sku for license key
     *
     * @return string
     */
    private function getSKU($sku) {if (strpos($sku,'_')!==false) {$sku=strtolower(substr($sku,0,strpos($sku,'_')));}return $sku;}

    /**
     * returns real sku for license key
     *
     * @return string
     */
    private function getWebsite($website) {$website = strtolower($website);$website=str_replace('https:','',str_replace('/','',str_replace('http:','',str_replace('www.', '', $website))));return $website;}
}