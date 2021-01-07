<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GlobalSiteTag\Model\Observer\Backend;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\Observer;

/**
 * Store data for Google gtagjs
 * Stored data will be used later in admin page
 *
 * Class SalesOrderCreate
 * @package Scommerce\GlobalSiteTag\Model\Observer\Backend
 */
class SalesOrderCreate implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepository;

    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    private $orderRepository;

    /** @var \Scommerce\GlobalSiteTag\Model\Session\BackendSession */
    private $session;

    /** @var \Scommerce\GlobalSiteTag\Helper\Data */
    private $helper;

    /** @var \Magento\Sales\Model\OrderFactory */
    protected $orderFactory;

    /**
     * SalesOrderCreate constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Scommerce\GlobalSiteTag\Model\Session\BackendSession $session
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Scommerce\GlobalSiteTag\Model\Session\BackendSession $session,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->session = $session;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param Observer $observer
     * @see \Magento\Sales\Model\Order::place()
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled() || !$this->helper->isDynamicRemarketingEnabled()) {
            return;
        }
        /** @var OrderInterface|Order $order */
        $order = $observer->getEvent()->getData('order');
        $this->session->setOrderData($this->makeOrderData($order));
    }

    /**
     * @param $orderId
     * @return mixed
     */
    protected function getOrderById($orderId) {
        $order = $this->orderFactory->create();
        if ($orderId) {
            $order->load($orderId);
        }
        return $order;
    }

    /**
     * @param OrderInterface|Order $order
     * @return array
     */
    private function makeOrderData($order)
    {
        return [
            'transaction_id' => $order->getIncrementId(),
            'value' => $order->getGrandTotal(),
            'currency' => $order->getOrderCurrencyCode(),
            'tax' => $order->getTaxAmount(),
            'shipping' => $order->getShippingAmount(),
            'items' => $this->makeOrderDataItems($order),
            'campaign' => [
                'source' => $this->helper->getBackendSource(),
                'medium' => $this->helper->getBackendMedium()
            ],
        ];
    }

    /**
     * @param OrderInterface|Order $order
     * @return array
     */
    private function makeOrderDataItems($order)
    {
        $items = [];
        foreach ($order->getAllItems() as $orderItem) {
            try {
                $items[] = $this->makeItem($orderItem);
            } catch (\Exception $e) {
                // Do nothing
            }
        }
        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item|\Magento\Sales\Api\Data\OrderItemInterface $item
     * @return array
     * @throws \Exception
     */
    private function makeItem($item)
    {
        $product = $this->productRepository->getById($item->getProductId());
        if (!($category = $item->getGoogleCategory())) {
            $category = $this->helper->getProductCategoryName($product);
        }
        return [
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'brand' => $this->helper->getBrand($product),
            'category' => $category,
            'variant' => $this->helper->getProductVariant($product, $item),
            'price' => ($this->helper->sendBaseData() ? $item->getBasePrice() : $item->getPrice()),
            'quantity' => $item->getQtyOrdered()
        ];
    }
}
