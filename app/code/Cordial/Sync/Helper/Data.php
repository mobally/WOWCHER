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


namespace Cordial\Sync\Helper;

class Data extends Config
{

    const XML_PATH_ACTIVE = 'cordial_sync/general/active';
    const XML_PATH_API_KEY = 'cordial_sync/general/api_key';
    const XML_PATH_CUSTOMER_ATTRIBUTES_MAP = 'cordial_sync/general/customer_attributes_map';

    const STEP = 10;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attrCollectionCustomer;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollection;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $entityAttribute;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Cordial\Sync\Model\Template
     */
    protected $template;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionCustomer
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Eav\Model\Entity\Attribute $entityAttribute
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionCustomer,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Cms\Model\Page $page,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Cordial\Sync\Model\Template $template
    ) {

        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attrCollectionCustomer = $attrCollectionCustomer;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->entityAttribute = $entityAttribute;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->registry = $registry;
        $this->jsonHelper = $jsonHelper;
        $this->_page = $page;
        $this->checkoutSession = $checkoutSession;
        $this->redirect = $redirect;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->orderRepository  = $orderRepository;
        $this->serializer = $serializer;
        $this->template = $template;
    }

    public function getCustomerAttributes($storeId = null)
    {

        /* @var $storeManager \Magento\Store\Model\StoreManagerInterface */
        $storeManager = $this->objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
        $store = $storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributes */
        $attributes = $this->attrCollectionCustomer->create()
            ->setWebsite($websiteId)
            ->load();

        return $attributes->toArray();
    }

    public function getProduct($productId)
    {
        $this->productFactory->create()->load($productId);
    }

    /**
     * Retrieve product stock qty
     *
     * @param Product $product
     * @return float
     */
    public function getProductStockQty($product)
    {
        return $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }

    /**
     * Load attribute data by code
     *
     * @param   mixed $entityType Can be integer, string, or instance of class Mage\Eav\Model\Entity\Type
     * @param   string $attributeCode
     * @return  \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttributeInfo($entityType, $attributeCode)
    {
        return $this->entityAttribute
            ->loadByCode($entityType, $attributeCode);
    }

    /**
     * Get category
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getCategory($product, $storeId)
    {
        $categoryIds = $product->getCategoryIds();
        $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();
        if (($key = array_search($rootCategoryId, $categoryIds)) !== false) {
            unset($categoryIds[$key]);
        }

        /**
         * @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection
         */
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addIdFilter($categoryIds);
        $category = $collection->getFirstItem();
        return $category->getName();
    }

    /**
     * Returns JS Event
     *
     * @return string
     */
    public function getJsEvent()
    {
        $data = [];
        $jsCode = "";
        $jsCodeArray = [];

        switch ($this->_request->getFullActionName()) {
            // category browse event
            case 'catalog_category_view':
                $currentCategory = $this->registry->registry('current_category');
                if ($currentCategory instanceof \Magento\Catalog\Model\Category) {
                    $data['category'] = $currentCategory->getName();
                    $data['url'] = $currentCategory->getUrl();
                    $data['images'] = ($currentCategory->getImageUrl()) ? [$currentCategory->getImageUrl()] : [];
                    $data['description'] = ($currentCategory->getDescription()) ? $currentCategory->getDescription() : '';

                    $properties = $this->jsonHelper->jsonEncode($data);
                    $jsCode = "cordial.event('browse-cat', $properties);";
                }
                break;

            // product browse event
            case 'catalog_product_view':
                $currentProduct = $this->registry->registry('current_product');
                // Product Page
                if ($currentProduct instanceof \Magento\Catalog\Model\Product) {
                    $data['title'] = $currentProduct->getName();
                    $data['url'] = $currentProduct->getProductUrl();
                    $data['sku'] = $currentProduct->getSku();
                    $data['description'] = $currentProduct->getDescription();
                    $data['price'] = $currentProduct->getFinalPrice();

                    $manageStock = $this->stockRegistry->getStockItem($currentProduct->getId())->getUseConfigManageStock();
                    if ($manageStock) {
                        $qty = number_format($this->stockRegistry->getStockStatus($currentProduct->getId(), $currentProduct->getStore()->getWebsiteId())->getQty());
                    } else {
                        $qty = 0;
                    }

                    $data['inventory'] = $qty;
                    $data['categories'] = $this->getCategory($currentProduct, $currentProduct->getStore()->getId());

                    $status = true;
                    if ($currentProduct->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
                        $status = false;
                    }
                    $data['status'] = $status;
                    $data['productID'] = $currentProduct->getEntityId();

                    $properties = $this->jsonHelper->jsonEncode($data);
                    $jsCode = "cordial.event('browse', $properties);";
                }
                break;

            // cms page view
            case 'cms_page_view':
                $cmsPageHelper = $this->objectManager->create(\Magento\Cms\Helper\Page::class);
                if ($this->_page->getId()) {
                    $pageId = $this->_page->getId();
                    $data['url'] = $cmsPageHelper->getPageUrl($this->_page->getIdentifier());
                    $data['title'] = $this->_page->getTitle();
                    $properties = $this->jsonHelper->jsonEncode($data);
                    $jsCode = "cordial.event('cms-page', $properties);";
                }
                break;

            // search event
            case 'catalogsearch_result_index':
                $searchHelper = $this->objectManager->create(\Magento\Search\Helper\Data::class);
                $data['term'] = $searchHelper->getEscapedQueryText();
                $data['result'] = $this->_urlBuilder->getCurrentUrl();
                $properties = $this->jsonHelper->jsonEncode($data);
                $jsCode = "cordial.event('search', $properties);";
                break;

            // cart even
            case 'checkout_cart_index':
                $jsCodeArray[] = "cordial.event('cart');";

            break;

            case 'checkout_onepage_success':
                $orderId    = $this->checkoutSession->getLastRealOrderId();
                $data['orderID'] = $orderId;
                $properties = $this->jsonHelper->jsonEncode($data);
                $jsCode = "cordial.event('purchase', $properties);";

                // cordial.order() - Tracking Purchases
                $data       = array();
                $orderId    = $this->checkoutSession->getLastOrderId();
                $order      = $this->orderRepository->get($orderId);
                $items      = array();

                $imageHelper        = $this->objectManager->create(\Magento\Catalog\Helper\Image::class);
                $data['orderID']    = $this->checkoutSession->getLastRealOrderId();

                foreach ($order->getAllItems() as $cartItem) {
                    $product = $cartItem->getProduct();

                    //'product_page_image_small'
                    $productImage = (string)$imageHelper->init($product, 'product_page_image_medium_no_frame')->setImageFile($product->getThumbnail())->getUrl();
                    (is_null($product->getDescription())) ? $productDescription = '' : $productDescription = $product->getDescription();

                    $items[] = [
                        'productID' => $product->getEntityId(),
                        'sku' => $product->getSku(),
                        'category' => $this->getCategory($product, $product->getStore()->getId()),
                        'name' => $product->getName(),
                        'images' => [$productImage],
                        'description' => $productDescription,
                        'qty'       => $cartItem->getQtyordered(),
                        'itemPrice' => $cartItem->getPrice(),
                        'url'       => $product->getProductUrl(),
                        'attr'      => []
                    ];
                }
                $data['items']  = $items;
                
                $addr = $order->getShippingAddress();
                $data['shippingAddress']    = array(
                    'name'          => $addr->getData('firstname') . ' ' . $addr->getData('lastname'),
                    'address'       => $addr->getData('street'),
                    'city'          => $addr->getCity(),
                    'state'         => $addr->getRegion(),
                    'postalCode'    => $addr->getData('postcode'),
                    'country'       => $addr->getData('country_id')
                );
                $addr = $order->getBillingAddress();
                $data['billingAddress']    = array(
                    'name'          => $addr->getData('firstname') . ' ' . $addr->getData('lastname'),
                    'address'       => $addr->getData('street'),
                    'city'          => $addr->getCity(),
                    'state'         => $addr->getRegion(),
                    'postalCode'    => $addr->getData('postcode'),
                    'country'       => $addr->getData('country_id')
                );
                $data['tax']    = $order->getTaxAmount();
                $data['status'] = 'new';
                $data['shippingAndHandling'] = $order->getShippingAmount();
                $properties = $this->jsonHelper->jsonEncode($data);
                $jsCode .= "cordial.order('add',{$properties});";

                $jsCodeArray[] = "cordial.clearcart();";
             break;

            case 'customer_account_logoutSuccess':
                $jsCode = "cordial.event('logout');";
                break;

            case 'customer_account_index':
                $lastUrl = $this->redirect->getRefererUrl();
                if (preg_match("#customer/account/login#", $lastUrl)) {
                    $jsCode = "cordial.event('login');";
                }
                break;

            default:
                break;
        }

        //Identifying a contact
        $jsContact = '';
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId()) {
            $email = $this->jsonHelper->jsonEncode($customer->getEmail());
            $jsContact = "cordial.identify($email);";
        }

        $jsCart = '';

        $cartItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
        if ($cartItems && is_object($cartItems[0])) {
            $jsCodeArray[] = "cordial.clearcart();";

            $items = [];
            /** @var \Magento\Catalog\Helper\Image */
            $imageHelper = $this->objectManager->create(\Magento\Catalog\Helper\Image::class);

            foreach ($cartItems as $cartItem) {
                if (!is_object($cartItem)) {
                    continue;
                }
                $product = $cartItem->getProduct();

                //'product_page_image_small'
                $productImage = (string)$imageHelper->init($product, 'product_page_image_medium_no_frame')->setImageFile($product->getThumbnail())->getUrl();
                (is_null($product->getDescription())) ? $productDescription = '' : $productDescription = $product->getDescription();

                $items[] = [
                    'productID' => $product->getEntityId(),
                    'sku' => $product->getSku(),
                    'category' => $this->getCategory($product, $product->getStore()->getId()),
                    'name' => $product->getName(),
                    'images' => [$productImage],
                    'description' => $productDescription,
                    'qty' => $cartItem->getQty(),
                    'itemPrice' => $cartItem->getPrice(),
                    'url' => $product->getProductUrl(),
                    'attr' => []
                ];
            }

            $properties = $this->jsonHelper->jsonEncode($items);
            $jsCodeArray[] = "cordial.cartitem('add', $properties);";
        }




        $jsCart = implode("\n  ", $jsCodeArray);
        $script = "\n";
        $script .= '<script type="text/javascript">' . "\n";
        $script .= 'function cordialMagento() {' . "\n";
        $script .= 'if (typeof cordial !== \'undefined\') {' . "\n";
        if (!empty($jsContact)) {
            $script .= "  " . $jsContact . "\n";
        }
        if (!empty($jsCode)) {
            $script .= "  " . $jsCode . "\n";
        }
        $script .= "  " . $jsCart . "\n";
        $script .= '}' . "\n";
        $script .= '}' . "\n";
        $script .= '</script>';

        return $script;
    }

    /**
     * Get customer attributes map for the given scope.
     * @param  $storeId
     * @return array
     */
    public function getCustomerAttributesMap($storeId = null)
    {
        $maps = [];
        $data = $this->getConfig(self::XML_PATH_CUSTOMER_ATTRIBUTES_MAP, $storeId);
        $mapsSaved = $this->serializer->unserialize($data);
        if (!$mapsSaved) {
            return $maps;
        }
        return $mapsSaved;
    }

    public function getCordialVariables()
    {
        $newCordialVars = [];
        if ($this->registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS)) {
            $cordialVars = $this->registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS);
        }

        return $cordialVars;
    }

    public function getParsedCordialVariables()
    {
        $newCordialVars = [];
        if ($this->registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS)) {
            $cordialVars = $this->registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS);
            foreach ($cordialVars as $key => $value) {
                if (is_object($value)) {
                    unset($cordialVars[$key]);
                }
            }
        }

        return $cordialVars;
    }

    /**
     * @return bool
     */
    public function routeEmail()
    {
        $templateVariables = $this->getCordialVariables();
        if (isset($templateVariables['storeId'])) {
            $storeId = $templateVariables['storeId'];
            $temlateId = $templateVariables['templateIdentifier'];
            if ($this->isEnabled($storeId) && $this->isRoute($storeId)) {
                $templateCordial = $this->template->loadByOrigCode($temlateId, $storeId);
                if ($templateCordial && !is_null($templateCordial['template_code'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getStoreId($message)
    {
        list($tag, $storeId) = explode(':', $message->getSubject());
        return $storeId;
    }

    /**
     * Returns a backtrace with just file a line number for compact logging
     *
     * For debug logging only.
     *
     * @return string
     */
    public function getSimplifiedBackTrace()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $simplifiedBacktrace = [];
        foreach ($backtrace as $index => $element) {
            if (isset($element['file'])) {
                $simplifiedElement = "#{$index} {$element['file']}";
                $simplifiedElement .= isset($element['line']) ? ": {$element['line']}" : '';
                $simplifiedBacktrace[] = $simplifiedElement;
            }
        }

        return (!empty($simplifiedBacktrace)) ? implode("\n\t", $simplifiedBacktrace) : '';
    }
}
