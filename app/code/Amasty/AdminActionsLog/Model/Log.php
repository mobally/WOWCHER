<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_AdminActionsLog
 */


namespace Amasty\AdminActionsLog\Model;

use Amasty\AdminActionsLog\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\Store;

class Log extends AbstractModel
{
    const OBJECT_PARAMETR_NAME = 'id';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Registry|mixed
     */
    protected $registryManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    protected function _construct()
    {
        $this->_init(ResourceModel\Log::class);
    }

    public function __construct(
        ObjectManagerInterface $objectManager,
        Session $authSession,
        Registry $coreRegistry,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Context $context,
        Data $helper,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context, $coreRegistry);
        $this->objectManager = $objectManager;
        $this->authSession = $authSession;
        $this->registryManager = isset($data['registry']) ? $data['registry'] : $coreRegistry;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function prepareLogData($object)
    {
        $data['date_time'] = $this->objectManager->get(\Magento\Framework\Stdlib\DateTime\DateTime::class)->gmtDate();

        if ($user = $this->authSession->getUser()) {
            $data['username'] = $user->getUserName();
        }

        $amauditCategory = $this->registryManager->registry('amaudit_category');
        $data['category'] = $amauditCategory == 'amaudit/actionslog'
            ? str_replace('_', '/', $object->getEventPrefix())
            : $amauditCategory;

        $data['category_name'] = $this->helper->getCategoryName($data['category']);
        $data['parametr_name'] = self::OBJECT_PARAMETR_NAME;
        $data['element_id'] = $this->getElementId($object);

        $action = $this->registryManager->registry('amaudit_action');
        $data['type'] = $this->getSaveType($object, $action);

        if ($data['category'] == 'sales/order_create') {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('increment_id', $data['element_id'], 'eq')
                ->create();
            $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();
            $data['element_id'] = $order->getId();
        }

        $data['item'] = $this->_getItem($object);

        if ($object->getStoreId() !== null) {
            $data['store_id'] = $object->getStoreId();
        } elseif ($object->getScopeId() !== null) {
            $data['store_id'] = $object->getScopeId();
        } elseif ($object->getStore() && $object->getStore()->getId()) {
            $data['store_id'] = $object->getStore()->getId();
        } else {
            $data['store_id'] = Store::DEFAULT_STORE_ID;
        }

        return $data;
    }

    public function getSaveType($object, $action): string
    {
        if ($action == 'restore') {
            $type = 'Restore';
        } else {
            if (($object->isObjectNew() && !($object instanceof \Magento\Framework\App\Config\Value))
                || $object instanceof \Magento\Quote\Model\Quote
                || $object instanceof \Magento\Sales\Model\Order\Invoice
            ) {
                $type = 'New';
            } elseif ($object->isDeleted()) {
                $type = 'Delete';
            } else {
                $type = 'Edit';
            }
        }

        return $type;
    }

    public function clearLog($fromObserver = true)
    {
        $logCollection = $this->getCollection();
        $where = [];

        if ($fromObserver) {
            $days = $this->scopeConfig->getValue('amaudit/log/log_delete_logs_after_days');
            $where['date_time < NOW() - INTERVAL ? DAY'] = $days;
        }

        $logCollection->getConnection()->delete($logCollection->getMainTable(), $where);
    }

    protected function getElementId($object)
    {
        $elementId = false;

        $specifiedClasses = [
            \Magento\Sales\Model\Order\Status\History::class => 'getParentId',
            \Magento\Quote\Model\Quote::class => 'getReservedOrderId',
            \Magento\Sales\Model\Order\Shipment::class => 'getOrderId',
            \Magento\Sales\Model\Order\Invoice::class => 'getOrderId',
            \Magento\Sales\Model\Order\Creditmemo::class => 'getOrderId',
            \Magento\Downloadable\Model\Link::class => 'getProductId',
            \Magento\Downloadable\Model\Sample::class => 'getProductId',
            \Magento\Store\Model\Website::class => 'getWebsiteId',
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class => 'getAttributeId',
            \Magento\Customer\Model\Group::class => 'getCustomerGroupId',
            \Magento\CatalogRule\Model\Rule::class => 'getRuleId',
            \Magento\Cms\Api\Data\PageInterface::class => 'getPageId',
        ];

        foreach ($specifiedClasses as $class => $function) {
            if (is_a($object, $class)) {
                $elementId = $object->$function();
                break;
            }
        }

        if (!$elementId) {
            $elementId = $object->getEntityId() ?: $object->getId();
        }

        return $elementId;
    }

    protected function _getItem($object)
    {
        $item = false;

        switch (true) {
            case $object instanceof \Magento\Catalog\Model\Product\Option:
            case $object instanceof \Magento\Downloadable\Model\Sample:
                if ($product = $object->getProduct()) {
                    $item = $product->getName();
                } elseif ($object->getProductId()) {
                    $item = $object->getProductId();
                }
                break;
            case $object instanceof \Magento\Customer\Model\Group:
                $item = $object->getCustomerGroupCode();
                break;
            case $object instanceof \Magento\Quote\Model\Quote:
                $item = __('Order') . ' #' . $object->getReservedOrderId();
                break;
            case $object instanceof \Magento\Sales\Model\Order\Invoice:
                $item = __('Invoice for Order') . ' #' . $object->getOrderId();
                break;
            case $object instanceof \Magento\Sales\Model\Order\Shipment:
                $item = __('Shipment for Order') . ' #' . $object->getOrderId();
                break;
            case $object instanceof \Magento\Sales\Model\Order\Creditmemo:
                $item = __('Credit Memo for Order') . ' #' .  $object->getOrderId();
                break;
            case $object instanceof \Magento\Sales\Model\Order:
                $item = __('Order') . ' #' .  $object->getRealOrderId();
                break;
            case $object instanceof \Magento\Tax\Model\Calculation\Rate:
                if (is_array($item)) {
                    $item = null;
                }

                break;
        }

        if (!$item) {
            if ($object->getName()) {
                $item = $object->getName();
            } elseif ($object->getTitle()) {
                $item = $object->getTitle();
            } elseif ($object->getCode()) {
                $item = $object->getCode();
            } elseif ($object->getTemplateCode()) {
                $item = $object->getTemplateCode();
            } elseif ($entity = $this->registryManager->registry('amaudit_entity_before_delete')) {
                $item = $entity->getName();

                if (!$item) {
                    $item = $entity->getTitle();
                }
            } elseif ($object->getParentId()) {
                $item = $object->getParentId();
            }
        }

        return $item;
    }
}
