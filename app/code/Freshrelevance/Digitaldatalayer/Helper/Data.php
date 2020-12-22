<?php

namespace Freshrelevance\Digitaldatalayer\Helper;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Locale\Resolver;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product as ProductModel;

class Data extends AbstractHelper
{
    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;
    private   $config;
    private   $pageTypeHelper;
    private   $productHelper;
    private   $mageMeta;
    private   $customerGroup;
    private   $locale;
    private   $order;
    private   $store;
    private   $productModel;
    private   $configModel;
    /**
     * @var Bool
     */
    private $_isScopePrivate;
    /**
     * @var Category
     */
    protected $catHelper;
    /**
     * @var null
     */
    protected $catIdToName = null;
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var CustomerSession
     */
    protected $checkoutSession;
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    public function __construct(
        Context $context,
        Config $config,
        PageType $pageTypeHelper,
        Product $productHelper,
        ProductMetadataInterface $mageMeta,
        Group $customerGroup,
        Resolver $locale,
        Order $order,
        StoreManagerInterface $store,
        ProductRepository $prodModel,
        Configurable $configModel,
        Category $catHelper,
        CustomerSession $session,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);

        $this->config           = $config;
        $this->pageTypeHelper   = $pageTypeHelper;
        $this->productHelper    = $productHelper;
        $this->mageMeta         = $mageMeta;
        $this->customerGroup    = $customerGroup;
        $this->locale           = $locale;
        $this->order            = $order;
        $this->store            = $store;
        $this->productModel     = $prodModel;
        $this->configModel      = $configModel;
        $this->urlInterface     = $context->getUrlBuilder();
        $this->requestInterface = $context->getRequest();
        $this->customerSession  = $session;
        $this->checkoutSession  = $checkoutSession;
        $this->_isScopePrivate  = true;
        $this->catHelper        = $catHelper;
    }

    public function getBaseData($pageCategoryTree = false)
    {
    $page_type = $this->pageTypeHelper->getCurrentPage();
    if($page_type != 'cms_index_index'){
    
        $result = [
            'page'          => $this->getPageData($pageCategoryTree),
            'pluginversion' => $this->getExtensionVersion(),
            'version'       => $this->getMagentoVersion(),
            'generatedDate' => (time() * 1000),
            'home' => $this->pageTypeHelper->getCurrentPage()
        ];
   }

        $isPageAvailable = $this->config->checkIsPageAvailableForDisposing($this->pageTypeHelper->getCurrentPage());
        $cartData        = $this->getCartData();

        if ($cartData && $isPageAvailable) {
            $result['cart'] = $cartData;
        }
        // Only show user data on uncacheable pages
        if (!$this->pageTypeHelper->isPageCacheable()) {
            $result['user'] = $this->getUserData();
        }
        return $result;
    }

    public function getExtensionVersion()
    {
        return '1.0.3';
    }

    public function getMagentoVersion()
    {
        $productMetadata = $this->mageMeta;
        return $productMetadata->getVersion();
    }

    public function getDdlCmsData()
    {
        $ddlData = $this->getBaseData();
        return $this->jsonSerialize($ddlData);
    }

    public function getDdlProductData(ProductModel $product)
    {
        if (!$product) {
            return null;
        }

        $ddlData         = $this->getBaseData();
        $isPageAvailable = $this->config->checkIsPageAvailableForDisposing($this->pageTypeHelper->getCurrentPage());
        if ($isPageAvailable) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$product_data = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
            $product            = $this->getProductData($product->getId());
            $ddlData['product'] = [$product];
            $ddlData['dealId'] = $product_data->getSku();
        }

        return $this->jsonSerialize($ddlData);
    }

    /**
     * @param $block
     * @param $categoryTree
     * @param bool $compare
     * @return false|mixed|string|void
     * @deprecated  -- no longer required after refactor, blocks themselves should responsible for their own data
     */
    public function getDdlProductCollectionData($block, $categoryTree, $compare = false)
    {
        $ddlData         = $this->getBaseData($categoryTree);
        $isPageAvailable = $this->config->checkIsPageAvailableForDisposing($this->pageTypeHelper->getCurrentPage());
        if ($block && $isPageAvailable) {
            // Only load Product Collection if page is enabled to prevent loading issues
            if ($categoryTree) {
                $productCollection = $block->getProductsCollection();
            } else {
                $productCollection = $block->getItems();
            }
            $ddlData['product'] = [];
            foreach ($productCollection as $item) {
                $ddlData['product'][] = $this->getProductData($item->getId());
            }
        }

        return $this->jsonSerialize($ddlData);
    }

    public function getDdlCartData()
    {
        $ddlData         = $this->getBaseData();
        $ddlData['cart'] = $this->getCartData();
        return $this->jsonSerialize($ddlData);
    }

    public function getDdlTransactionData()
    {
        $ddlData                = $this->getBaseData();
        $ddlData['transaction'] = $this->getTransactionData();

        return $this->jsonSerialize($ddlData);
    }

    public function getUserData()
    {
        $userData               = [];
        $userData['profile']    = [];
        $profile                = [];
        $profile['profileInfo'] = [];
        $customerSession        = $this->customerSession;
        $user                   = $customerSession->getCustomer();
        if ($user) {
            $user_id   = $user->getEntityId();
            $firstName = $user->getFirstname();
            $lastName  = $user->getLastname();
            $email     = $user->getEmail();
            $groupId   = $user->getGroupId();
            $groupCode = $this->customerGroup->load($groupId)->getCustomerGroupCode();

            if ($user_id) {
                $profile['profileInfo']['profileID'] = (string)$user_id;
                if ($this->config->getUserGroupExposure() == 1) {
                    $profile['profileInfo']['segment']['userGroupId'] = $groupId;
                    $profile['profileInfo']['segment']['userGroup']   = $groupCode;
                }
            }
            if ($firstName) {
                $profile['profileInfo']['userFirstName'] = $firstName;
            }
            if ($lastName) {
                $profile['profileInfo']['userLastName'] = $lastName;
            }
            if ($email) {
                $profile['profileInfo']['email'] = $email;
            }
        }
        $profile['profileInfo']['language']        = $resolver = $this->locale->getLocale();
        $profile['profileInfo']['returningStatus'] = $user ? 'true' : 'false';
        array_push($userData['profile'], $profile);

        return $userData;
    }

    public function getAddress($address)
    {
        $billing = [];
        if ($address) {
            $billing['line1']         = $address->getName();
            $billing['line2']         = $address->getStreetFull() ? $address->getStreetFull() : $address->getStreet()['0'];
            $billing['city']          = $address->getCity();
            $billing['postalCode']    = $address->getPostcode();
            $billing['country']       = $address->getCountryId();
            $state                    = $address->getRegion();
            $billing['stateProvince'] = $state ? $state : '';
        }
        return $billing;
    }

    public function getTransactionData()
    {
        $order       = $this->checkoutSession->getLastRealOrder();
        $orderId     = $order->getId();
        $transaction = [];
        if ($orderId) {
            // General details
            $transaction['transactionID']             = $order->getIncrementId();
            $transaction['total']                     = [];
            $transaction['total']['currency']         = $order->getOrderCurrencyCode();
            $transaction['total']['basePrice']        = (float)$order->getSubtotal();
            $transaction['total']['transactionTotal'] = (float)$order->getGrandTotal();
            $transaction['total']['shipping']         = (float)$order->getShippingAmount();
            $transaction['total']['shippingMethod']   = $order->getShippingMethod() ? $order->getShippingMethod() : '';

            $voucher                                 = $order->getCouponCode();
            $transaction['total']['voucherCode']     = $voucher ? $voucher : "";
            $voucher_discount                        = -1 * $order->getDiscountAmount();
            $transaction['total']['voucherDiscount'] = $voucher_discount ? $voucher_discount : 0;

            // Get addresses
            $transaction['profile'] = [];
            if ($order->getBillingAddress()) {
                $billingAddress                    = $order->getBillingAddress();
                $transaction['profile']['address'] = $this->getAddress($billingAddress);
            }
            if ($order->getShippingAddress()) {
                $shippingAddress                           = $order->getShippingAddress();
                $transaction['profile']['shippingAddress'] = $this->getAddress($shippingAddress);
            }
            // Add email
            if ($order->getCustomerEmail()) {
                $transaction['profile']['profileInfo']          = [];
                $transaction['profile']['profileInfo']['email'] = $order->getCustomerEmail();
            }
            // Get items
            $items                = $order->getAllVisibleItems();
            $itemsData            = $this->getItemsData($items);
            $transaction['items'] = $itemsData;
        }

        return $transaction;
    }

    public function getCartData()
    {
        $quote = $this->checkoutSession->getQuote();
        $cart  = [];

        if ($quote) {
            $cart['cartID'] = $quote->getId();
            // Get Quote Details
            if ($quote->getSubtotal()) {
                $cart['price']['basePrice'] = (float)$quote->getSubtotal();
            } elseif ($quote->getBaseSubtotal()) {
                $cart['price']['basePrice'] = (float)$quote->getBaseSubtotal();
            } else {
                $cart['price']['basePrice'] = 0.0;
            }
            if ($quote->getShippingAddress()->getCouponCode()) {
                $cart['price']['voucherCode']     = $quote->getShippingAddress()->getCouponCode();
                $cart['price']['voucherDiscount'] = abs((float)$quote->getShippingAddress()->getDiscountAmount());
            }
            $cart['price']['currency'] = $quote->getQuoteCurrencyCode();
            if ($cart['price']['basePrice'] > 0.0) {
                $taxRate                  = (float)$quote->getShippingAddress()->getTaxAmount() / $quote->getSubtotal();
                $cart['price']['taxRate'] = round($taxRate, 3);
            }
            if ($quote->getShippingAmount()) {
                $cart['price']['shipping']       = (float)$quote->getShippingAmount();
                $cart['price']['shippingMethod'] = $quote->getShippingMethod();
                $cart['price']['priceWithTax']   = (float)$quote->getGrandTotal();
            } else {
                $cart['price']['priceWithTax'] = (float)$cart['price']['basePrice'];
            }
            if ($quote->getData()) {
                $getData = $quote->getData(); // To resolve a error on some versions of PHP.
                if (array_key_exists('grand_total', $getData)) {
                    $cart['price']['cartTotal'] = (float)$getData['grand_total'];
                } else {
                    $cart['price']['cartTotal'] = (float)$cart['price']['priceWithTax'];
                }
            } else {
                $cart['price']['cartTotal'] = (float)$cart['price']['priceWithTax'];
            }

            // Line items
            $items = $quote->getAllVisibleItems();
            if (!empty($items)) {
                $cart['items'] = $this->getItemsData($items);
            } else {
                return false;
            }
        }
        return $cart;
    }

    public function getCheckoutData()
    {
        return $this->getCartData();
    }

    public function getSelectedAttributeValues($values)
    {
        $returnDict = [];
        foreach ($values as $value) {
            $returnDict[$value['label']] = $value['value'];
        }
        return $returnDict;
    }

    public function getAttributeIdDict($attribute)
    {
        $attributeDict = [];
        foreach ($attribute['values'] as $opt) {
            $attributeDict[$opt['store_label']] = $opt['value_index'];
        }
        return $attributeDict;
    }

    public function getItemsData($items)
    {
        $store = $this->store->getStore();

        $lineItems = [];
        // Collect all productIDs so we can get categories from them at once
        $productIDs = [];
        foreach ($items as $item) {
            $productIDs[] = $item->getProductId();
        }

        //Lazy load all the relevant categories and products first.
        $categoriesPerProduct = $this->catHelper->getCategoriesByProductId($productIDs);
        $products             = $this->productHelper->getProductsByIDS($productIDs);

        foreach ($items as $item) {
            $itemData   = [];
            $itemPrice  = [];
            $productId  = $item->getProductId();
            $categories = [];
            if (array_key_exists($item->getProductId(), $products)){
                // Check to see if key exists to handle flat catalog bug
                try {
                    $product = $products[$item->getProductId()];

                    foreach ($categoriesPerProduct[$productId] as $index => $cat) {
                        if ($index == 0) {
                            $categories['primaryCategory'] = $this->catHelper->getCatNameById($cat);
                        } else {
                            $categories['subCategory' . $index] = $this->catHelper->getCatNameById($cat);
                        }
                    }
                    $categories['productType'] = $product->getTypeId();

                    $itemPrice['basePrice']    = (float)$item->getPrice();
                    $itemPrice['priceWithTax'] = (float)$item->getPriceInclTax();
                    $itemPrice['currency']     = $store->getCurrentCurrency()->getCode();
                    //passing in the preloaded product
                    $productData = $this->getProductData($productId, $item, $product);

                    $itemData['productInfo'] = $productData['productInfo'];
                    if (isset($productData['linkedProduct'])) {
                        $itemData['linkedProduct'] = $productData['linkedProduct'];
                    }
                    $itemData['price']    = $itemPrice;
                    $itemData['quantity'] = (float)($item->getQty());

                    $itemData['category'] = $categories;
                    $configCode           = Configurable::TYPE_CODE;
                    if ($product->getTypeId() == $configCode) {
                        $itemData['attributes']['configurable_options'] = $this->getConfigOptions($item);
                    }
                    if ($product->getTypeId() == 'bundle') {
                        $itemData['attributes']['bundle_options'] = $this->getBundleOptions($item);
                    }
                    $custom_options = $product->getTypeInstance(true)->getOrderOptions($product);
                    if (isset($custom_options['options'])) {
                        $itemData['attributes']['custom_options'] = $this->getCustomOptions($item);
                    }
                    array_push($lineItems, $itemData);
                } catch (Exception $e) {
                    // Catch exceptions in case one bad product throws whole extension off
                }
            }
        }
        return $lineItems;
    }

    public function getProductData($productId, $item = null, $product = null)
    {
        $productModel = $this->productModel;
        $product      = $product !== null ? $product : $productModel->getById($productId);
        $productData  = $this->productHelper->getProductData($product);
        $configCode   = Configurable::TYPE_CODE;
        $groupedCode  = Grouped::TYPE_CODE;
        // Check if Linked Product Exposure is enabled
        if ($this->config->linkedProductsAvailable() != 0) {
            if ($product->getTypeId() == $configCode) {
                if ($item) {
                    $productData['linkedProduct'] = $this->productHelper->getConfigurableSelectedLinkedProducts($item);
                } else {
                    $productData['linkedProduct'] = $this->productHelper->getConfigurableLinkedProducts($product);
                }
            } elseif ($product->getTypeId() == 'bundle') {
                if ($item) {
                    $productData['linkedProduct'] = $this->productHelper->getBundleSelectedLinkedProducts($item);
                } else {
                    $productData['linkedProduct'] = $this->productHelper->getBundleLinkedProducts($product);
                }
            } elseif ($product->getTypeId() == $groupedCode) {
                // Only visible on Grouped Product Browse Pages
                $productData['linkedProduct'] = $this->productHelper->getGroupedLinkedProducts($product);
            }
        }
        return $productData;
    }

    public function getPageData($categoryTree = [])
    {
        $urlInterface         = $this->urlInterface;
        $localeResolver       = $this->locale;
        $pageData             = [];
        $pageData['pageInfo'] = [];
        $pageData['category'] = [];
        $referringURL         = $this->requestInterface->getServer('HTTP_REFERER');

        $pageData['pageInfo']['pageName']       = $this->pageTypeHelper->getPageTitle();
        $pageData['pageInfo']['destinationURL'] = $urlInterface->getCurrentUrl();
        if ($referringURL) {
            $pageData['pageInfo']['referringURL'] = $referringURL;
        }
        $pageData['pageInfo']['language'] = $localeResolver->getLocale();
        if ($categoryTree) {
            $pageData['category']['primaryCategory'] = $categoryTree['2'];
            if (count($categoryTree) > 1) {
                $i = count($categoryTree);
                while ($i > 1) {
                    $pageData['category']['subCategory' . ($i - 1)] = $categoryTree[$i + 1];
                    $i--;
                }
            }
        }
        $pageData['category']['pageType'] = $this->pageTypeHelper->getPageType();
        return $pageData;
    }

    /**
     * Returns purchase complete query string
     * @return string
     */
    public function getPurchaseCompleteQs()
    {
        $customerSession = $this->customerSession;
        $order           = $this->customerSession->getLastRealOrder();
        $orderId         = false;
        if ($order->getId()) {
            $orderId = $order->getId();
            $email   = $order->getCustomerEmail();
        } else {
            $email = $customerSession->getCustomer()->getEmail();
        }
        $qs = "e=" . urlencode($email);

        if ($orderId) {
            $qs = $qs . "&r=" . urlencode($orderId);
        }

        return $qs;
    }

    private function getConfigOptions($item)
    {
        $returnOptions          = [];
        $selectedAttributes     = $this->configModel->getSelectedAttributesInfo($item->getProduct());
        $configurableAttributes = $this->configModel->getConfigurableAttributesAsArray($item->getProduct());
        if ($selectedAttributes) {
            $i          = 0;
            $valuesDict = $this->getSelectedAttributeValues($selectedAttributes);
            foreach ($configurableAttributes as $id => $attribute) {
                $attrIdDict = $this->getAttributeIdDict($attribute);
                $opt        = [
                    'id'   => $id,
                    'name' => $attribute['frontend_label']
                ];
                try {
                    if ($attribute['store_label']) {
                        if (array_key_exists($attribute['store_label'], $valuesDict)) {
                            $opt['value'] = $valuesDict[$attribute['store_label']];
                        }
                        if (array_key_exists($opt['value'], $attrIdDict)) {
                            $opt['val_id'] = $attrIdDict[$opt['value']];
                        }
                    }
                } catch (Exception $e) {
                }
                array_push($returnOptions, $opt);
                $i++;
            }
        }
        return $returnOptions;
    }

    private function getBundleOptions($item)
    {
        $returnOptions = [];
        $orderOptions  = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        $bundleOptions = $orderOptions['bundle_options'];
        $buyRequest    = $orderOptions["info_buyRequest"]["bundle_option"];
        foreach ($bundleOptions as $option) {
            try {
                array_push($returnOptions, [
                    'id'     => $option['option_id'],
                    'name'   => $option['label'],
                    'val_id' => $buyRequest[$option['option_id']],
                    'value'  => $option['value']
                ]);
            } catch (Exception $e) {
            }
        }
        return $returnOptions;
    }

    private function getCustomOptions($item)
    {
        $returnOptions = [];
        $customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct())['options'];
        foreach ($customOptions as $option) {
            array_push($returnOptions, [
                'id'     => $option['option_id'],
                'name'   => $option['label'],
                'val_id' => $option['option_value'],
                'value'  => $option['value']
            ]);
        }
        return $returnOptions;
    }

    /**
     * Helper to serialize data,
     * and provide exception for failure
     * @param array $data
     * @return null|string
     * @throws InvalidArgumentException - if json encoding fails
     */
    public function jsonSerialize(array $data)
    {
        $result = json_encode($data);
        if (false === $result) {
            throw new InvalidArgumentException("Unable to serialize value. Error: " . json_last_error_msg());
        }
        // if we get back an array, force an object.
        if (is_array($result)) {
            $result = json_encode($result, JSON_FORCE_OBJECT);
        }
        if (false === $result) {
            throw new InvalidArgumentException("Unable to serialize value. Error: " . json_last_error_msg());
        }
        return $result;
    }
}
