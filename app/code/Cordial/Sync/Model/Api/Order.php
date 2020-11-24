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

class Order extends Client
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Cordial\Sync\Model\Touched
     */
    protected $touched;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $entity;
    /**
     * @param \Cordial\Sync\Helper\Data $helper
     * @param \Cordial\Sync\Model\Log $log
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Cordial\Sync\Model\Touched $touched
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Catalog\Model\Product $productModel
     */
    public function __construct(
        \Cordial\Sync\Helper\Data $helper,
        \Cordial\Sync\Model\Log $log,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Eav\Model\Entity $entity,
        \Cordial\Sync\Model\Touched $touched,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Model\Product $productModel
    ) {

        $this->helper = $helper;
        $this->log = $log;
        $this->jsonHelper = $jsonHelper;
        $this->entity = $entity;
        $this->touched = $touched;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->productModel = $productModel;
    }

    /**
     * Get Order EntityType
     */
    protected function _getEntityTypeId()
    {
        return $this->entity->setType('order')->getTypeId();
    }

    /**
     * Create order
     *
     * @param int|Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function create($order, $update = false)
    {
        if (is_numeric($order)) {
            $order = $this->orderRepository->get($order);
        }
        if (!$order instanceof \Magento\Sales\Model\Order) {
            return false;
        }

        $sync = $order->getCustomAttribute(Config::ATTR_CODE);
        if (!is_null($sync)) {
            if (!$sync->getValue(Config::ATTR_CODE)) {
                $this->logger->debug('Order with id '.$order->getIncrementId() .' is marked as Cordial Sync Ignore');
                return false;
            }
        }

        $path       = "orders";
        $dateTime   = new \DateTime($order->getCreatedAt());
        $createdAt  = $dateTime->format(\Cordial\Sync\Model\Api\Config::DATE_FORMAT);

        $data = [
            "orderID"               => $order->getIncrementId(),
            "email"                 => $order->getCustomerEmail(),
            "status"                => (string)$order->getStatusLabel(),
            "storeID"               => $order->getStoreId(),
            "purchaseDate"          => $createdAt,
            "billingAddress"        => $this->_getBillingAddress($order),
            "shippingAddress"       => $this->_getShippingAddress($order),
            "items"                 => $this->_getItems($order),
            "tax"                   => $order->getTaxAmount(),
            "shippingAndHandling"   => $order->getShippingDescription()
        ];

        if ($order->getCustomerId()) {
            $data['customerID'] = $order->getCustomerId();
        }

        if ($update == false) {
            $this->_createContact($createdAt, $order);
            $result = $this->_request('POST', $path, $data);
        }

        if ($update || !$result) {
            $path = "orders/" . $order->getIncrementId();

            // HOTFIX: MAG-14 BUG: Magento Integration not attributing revenue to messages
            // Thu Dec 19 15:19:17 2019
            $result0 = $this->_request('GET', $path);
            $data['mcID']   = @$result0['mcID'];
            $data['cID']    = @$result0['cID'];
            $data['msID']   = @$result0['msID'];

            if ($data['cID']) unset($data['email']);
            // HOTFIX

            $result = $this->_request('PUT', $path, $data);
        }
        
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Get items
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getItems($order)
    {
        $items = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /**
         * @var $apiProduct \Cordial\Sync\Model\Api\Product
         */
        $apiProduct = $objectManager->create('\Cordial\Sync\Model\Api\Product');

        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $product = $this->productModel->setStoreId($order->getStoreId())->load($item->getProductId());
            $productUrl = $product->getUrlModel()->getUrl($product);
            if (is_null($productUrl)) {
                $productUrl = '';
            }
            $productUrl = preg_replace('/\?.*/', '', $productUrl);


            $items[] = [
                'productID' => $item->getProductId(),
                "description" => ($product->getDescription()) ? $product->getDescription() : '',
                "sku" => $item->getSku(),
                "category" => $this->helper->getCategory($product, $order->getStoreId()),
                "name" => $item->getName(),
                "qty" => $item->getQtyOrdered(),
                "itemPrice" => $item->getPrice(),
                "url" => $productUrl,
                "productType" => $apiProduct->getProductType($item->getProductType()),
            ];
        }

        return $items;
    }

    /**
     * Get billing Address data
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getBillingAddress($order)
    {
        $data = [];
        /* @var $address \Magento\Sales\Api\Data\OrderAddressInterface|null Billing address. Otherwise, null. */
        $address = $order->getBillingAddress();
        if ($address) {
            $countryId = $address->getCountryId();
            $street = $address->getStreet();
            if (is_array($street)) {
                $street = trim(implode("\n", $street));
            }

            $data = [
                "name" => $address->getFirstname() . ' ' . $address->getLastname(),
                "address" => $street,
                "city" => $address->getCity(),
                "state" => $address->getRegion(),
                "postalCode" => $address->getPostcode(),
                "country" => $address->getCountryId()
            ];
        }
        return $data;
    }

    /**
     * Get shipping Address data
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    protected function _getShippingAddress($order)
    {
        $data = [];
        $address = $order->getShippingAddress();
        if ($address) {
            $countryId = $address->getCountryId();
            $street = $address->getStreet();
            if (is_array($street)) {
                $street = trim(implode("\n", $street));
            }
            $data = [
                "name" => $address->getFirstname() . ' ' . $address->getLastname(),
                "address" => $street,
                "city" => $address->getCity(),
                "state" => $address->getRegion(),
                "postalCode" => $address->getPostcode(),
                "country" => $address->getCountryId()
            ];
        }
        return $data;
    }

    /**
     * Delete order
     *
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function delete($order, $storeId = null)
    {
        if (!$order instanceof \Magento\Sales\Model\Order) {
            return false;
        }

        $orderId = $order->getEntityId();
        $path = "orders/" . $order->getIncrementId();
        $result = $this->_request('DELETE', $path);
        if (!$result) {
            return false;
        }

        return true;
    }


    public function update($order)
    {
        return $this->create($order, true);
    }

    /**
     * Unsync order, alias for delete order
     *
     * @param int|Mage_Customer_Model_Customer $customer
     * @return boolean
     */
    public function unsync($order)
    {
        if (is_numeric($order)) {
            $order = $this->orderRepository->get($order);
        }

        if (!$order instanceof \Magento\Sales\Model\Order) {
            return false;
        }

        $orderId = $order->getEntityId();
        $path = "orders/" . $order->getIncrementId();

        $result = $this->_request('DELETE', $path);
        if (!$result) {
            return false;
        }

        return true;
    }

    protected function _createContact($purchaseDate, $order)
    {
        $path = "contacts";
        $result = false;
        $data = ['lastPurchase' => $purchaseDate];

        if ($customerId = $order->getCustomerId()) {
            $customerEntityTypeId = $this->_getCustomerEntityTypeId();
            $touched = $this->touched->loadByUniqueKey($customerId, $customerEntityTypeId, $this->storeId);
            if ($touched->getExternalId()) {
                $pathUpdate = $path . '/' . rawurlencode($touched->getExternalId());
                $result = $this->_request('PUT', $pathUpdate, $data);
            }
        }

        //Guest || can't update customer
        if (!$result) {
            $data['channels'] = [
                'email' => [
                    'address' => $order->getCustomerEmail()
                ]
            ];

            //get some info about contact from order
            $customerAttributesMap = $this->helper->getCustomerAttributesMap($this->storeId);
            $address = $order->getBillingAddress();
            if (!empty($customerAttributesMap)) {
                foreach ($customerAttributesMap as $attribute) {
                    if ($address->getData($attribute['magento'])) {
                        $data[$attribute['cordial']] = $address->getData($attribute['magento']);
                    }
                }
            }
            $result = $this->_request('POST', $path, $data);
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Get Customer EntityType
     */
    protected function _getCustomerEntityTypeId()
    {
        return $this->entity->setType('customer')->getTypeId();
    }
}
