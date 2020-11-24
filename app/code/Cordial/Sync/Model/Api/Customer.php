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

class Customer extends Client
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $subscriber;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Cordial\Sync\Model\Touched
     */
    protected $touched;

    /**
     * @param \Cordial\Sync\Helper\Data $helper
     * @param \Cordial\Sync\Model\Log $log
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Cordial\Sync\Model\Touched $touched
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Cordial\Sync\Helper\Data $helper,
        \Cordial\Sync\Model\Log $log,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Eav\Model\Entity $entity,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Cordial\Sync\Model\Touched $touched,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->helper = $helper;
        $this->log = $log;
        $this->jsonHelper = $jsonHelper;
        $this->_entity = $entity;
        $this->customerFactory = $customerFactory;
        $this->subscriber = $subscriber;
        $this->wishlistFactory = $wishlistFactory;
        $this->addressRepository = $addressRepository;
        $this->touched = $touched;
        $this->_countryFactory = $countryFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Get Customer EntityType
     */
    protected function _getEntityTypeId()
    {
        return $this->_entity->setType('customer')->getTypeId();
    }

    /**
     * Widget Back in Stock Alert
     *
     * @return boolean
     */
    public function widgetAlert($email, $productId, $storeId = null)
    {
        try {
            $customerId = $email;
            $path = "contacts";

            $data = [
                'channels' => [
                    'email' => [
                        'address' => $email,
                        'subscribeStatus' => 'subscribed'
                    ]
                ],
                'magentoAlerts' => [
                    'add' => [$productId]
                ]
            ];
            $result = $this->_request('POST', $path, $data);
            if (!$result) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('widgetAlertCallback::' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create Customer
     *
     * @param int|Mage_Customer_Model_Customer $customer
     * @return boolean
     */
    public function create($customer, $update = false)
    {
        //Magento\Customer\Api\CustomerRepositoryInterface
        if (is_numeric($customer)) {
            $customer = $this->customerFactory->create()->load($customer);
        }
        if (!$customer instanceof \Magento\Customer\Model\Customer) {
            return false;
        }

        if (!$customer->getData(Config::ATTR_CODE)) {
            return false;
        }

        $customerId = $customer->getId();
        $path = "contacts";
        $subscriber = $this->subscriber->loadByEmail($customer->getEmail());

        switch ($subscriber->getStatus()) {
            case \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED:
                $subscribeStatus = 'subscribed';
                break;

            case \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED:
                $subscribeStatus = 'unsubscribed';
                break;

            case \Magento\Newsletter\Model\Subscriber::STATUS_UNCONFIRMED:
            case \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE:
            default:
                $subscribeStatus = 'none';
                break;
        }
        $data = [
            'channels' => [
                'email' => [
                    'address' => $customer->getEmail(),
                    'subscribeStatus' => $subscribeStatus
                ]
            ],

        ];
        if ($subscribeStatus == 'subscribed') {
            $data['forceSubscribe'] = true;
            $data['promotional'] = true;
        }
//var_dump(51815542, $customerId, $subscriber->getStatus(), $customer->getEmail(), $subscribeStatus, debug_backtrace(2), $data);exit;

        $wishlistProducts = [];
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customer->getId());
        if ($wishlist) {
            foreach ($wishlist->getItemCollection() as $wishlistItem) {
                $wishlistProducts[] = $wishlistItem->getProductId();
            }
        }
        $data['m_wishlist'] = $wishlistProducts;

        $purchaseDate = $this->getCustomerLastPurchase($customer->getId());
        if ($purchaseDate) {
            $dateTime = new \DateTime($purchaseDate);
            $createdAt = $dateTime->format(\Cordial\Sync\Model\Api\Config::DATE_FORMAT);
            $data['lastPurchase'] = $createdAt;
        }

        $customerAttributesMap = $this->helper->getCustomerAttributesMap($this->storeId);

        if (!empty($customerAttributesMap)) {
            foreach ($customerAttributesMap as $attribute) {
                if ($customer->getData($attribute['magento'])) {
                    $geoType = false;

                    $magentoAttribute = $this->helper->getAttributeInfo($this->_getEntityTypeId(), $attribute['magento']);
                    if ($magentoAttribute) {
                        if ($magentoAttribute->getAttributeCode() == 'default_billing' || $magentoAttribute->getAttributeCode() == 'default_shipping') {
                            $geoType = true;
                        }
                    } else {
                        continue;
                    }

                    if ($geoType) {
                        $options = [];
                        try {
                            $address = $this->addressRepository->getById($customer->getData($attribute['magento']));

                            if ($address) {
                                if ($address->getPostcode()) {
                                    $options['postal_code'] = $address->getPostcode();
                                }
                                if ($address->getCountryId()) {
                                    $options['country'] = $this->_countryFactory->create()->setId($address->getCountryId())->getName();
                                    $options['countryISO'] = $address->getCountryId();
                                }
                                if ($address->getRegion()) {
                                    $options['state'] = $address->getRegion()->getRegion();
                                }
                                if ($address->getCity()) {
                                    $options['city'] = $address->getCity();
                                }
                                if ($address->getStreet()) {
                                    $street = $address->getStreet();
                                    if (!is_null($street)) {
                                        $options['street_address'] = $street[0];
                                        if (isset($street[1])) {
                                            $options['street_address2'] = $street[1];
                                        }
                                    } else {
                                        $options['street_address'] = '';
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $this->logger->debug($e->getMessage());
                        }

                        foreach ($options as $key => $value) {
                            if (is_null($value)) {
                                $options[$key] = '';
                            }

                            if (is_array($value) && empty($value)) {
                                $options = '';
                            }

                            if (is_array($value) && isset($value[0])) {
                                $options = $value[0];
                            }
                        }

                        $data[$attribute['cordial']] = $options;
                    } else {
                        $data[$attribute['cordial']] = $customer->getData($attribute['magento']);
                    }
                }
            }
        }

        $result		= false;
        $touched	= $this->touched->loadByUniqueKey($customerId, $this->_getEntityTypeId(), $this->storeId);

        if ($touched->getExternalId()) {
            $pathUpdate = $path . '/' . rawurlencode($touched->getExternalId());
            $result = $this->_request('GET', $pathUpdate, $data);
        }

        if (!$result) {
            $result = $this->_request('POST', $path, $data);
        }

        if (!$result) {
            return false;
        }

        $touched->setExternalId($customer->getEmail());
        $touched->save();

        return true;
    }

    public function update($customer)
    {
        return $this->create($customer, true);
    }

    /**
     * Return last purchase date.
     *
     * @param  $customerId
     * @param int $storeId
     * @return string
     */
    protected function getCustomerLastPurchase($customerId)
    {
        $purchaseDate = '';
        $collection = $this->orderCollectionFactory->create();
        $order = $collection->addFieldToFilter('store_id', $this->storeId)
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'DESC')
            ->getFirstItem();

        if ($order) {
            $purchaseDate = $order->getCreatedAt();
        }

        return $purchaseDate;
    }


    /**
     * Delete Customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return boolean
     */
    public function delete($customer)
    {
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $customerEmail = $customer->getData('email');
            $customer = $customer->getId();
        }

        $touched = $this->touched->loadByUniqueKey($customer, $this->_getEntityTypeId(), $this->storeId);
        if ($touched->getExternalId()) {
            $customerEmail = $touched->getExternalId();
        }

        if (!isset($customerEmail)) {
            return true;
        }

        $customerEmail = rawurlencode($customerEmail);

        $path = "contacts/$customerEmail";

        $dateTime = new \DateTime();
        $createdAt = $dateTime->format(\Cordial\Sync\Model\Api\Config::DATE_FORMAT);
        $data['magentoDeleted'] = $createdAt;
        $deleted = $this->_request('PUT', $path, $data);

        if (!$deleted) {
            return false;
        }

        return true;
    }

    public function updateWishlist($email, $productId, $action, $storeId = null)
    {
        try {
            $customerId = $email;
            $path = "contacts";

            $data = [
                'channels' => [
                    'email' => [
                        'address' => $email
                    ]
                ]
            ];

            $data['m_wishlist'] = [
                $action => [$productId]
            ];

            $setApiKey = $this->_setApiKey($storeId);
            $result = $this->_request('POST', $path, $data);
            if (!$result) {
                return false;
            }

            $touched = $this->touched->sync($customerId, $this->_getEntityTypeId(), $storeId);
            return true;
        } catch (\Exception $e) {
            $this->logger->debug('widgetAlertCallback::' . $e->getMessage());
            return false;
        }
    }
}
