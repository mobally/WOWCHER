<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GlobalSiteTag\Model\Observer\Backend;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

/**
 * Store data for Google gtagjs
 * Stored data will be used later in admin page
 *
 * Class SalesOrderCreditmemoRefund
 * @package Scommerce\GlobalSiteTag\Model\Observer\Backend
 */
class SalesOrderCreditmemoRefund implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepository;

    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    private $orderRepository;

    /** @var \Scommerce\GlobalSiteTag\Model\Session\BackendSession */
    private $session;

    /** @var \Scommerce\GlobalSiteTag\Helper\Data */
    private $helper;

    /** @var \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory */
    private $transactions;

    /** @var \Magento\Sales\Model\OrderFactory */
    protected $orderFactory;

    /**
     * SalesOrderCreditmemoRefund constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Scommerce\GlobalSiteTag\Model\Session\BackendSession $session
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Scommerce\GlobalSiteTag\Model\Session\BackendSession $session,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions
    ) {
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->session = $session;
        $this->helper = $helper;
        $this->transactions = $transactions;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param Observer $observer
     * @see \Magento\Sales\Model\Order\Creditmemo\RefundOperation::execute()
     * @see \Magento\Sales\Model\Service\CreditmemoService::refund()
     */
    public function execute(Observer $observer)
    {
        if (! $this->helper->isEnabled()) {
            return;
        }
        /** @var CreditmemoInterface|Creditmemo $memo */
        $memo = $observer->getEvent()->getData('creditmemo');
        $this->session->setRefundData($this->makeRefundData($memo));
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getTransactionId($order)
    {
        $default = $order->getIncrementId();
        $payment = $order->getPayment();
        if (!$payment) {
            return $default;
        }
        $id = $payment->getLastTransId();
        return $id ? $id : $default;
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
     * @param CreditmemoInterface|Creditmemo $memo
     * @return array
     * @see \Scommerce\GlobalSiteTag\Model\Session\BackendSession::getRefundData()
     */
    private function makeRefundData($memo)
    {
        $orderId = $memo->getOrderId();
        $order = $this->getOrderById($orderId);
        return [
            'transaction_id' => $this->getTransactionId($order),
            'value' => $memo->getGrandTotal(),
            'currency' => $memo->getOrderCurrencyCode(),
            'tax' => $memo->getTaxAmount(),
            'shipping' => $memo->getShippingAmount(),
            'items' => $this->makeRefundDataItems($memo),
        ];
    }

    /**
     * @param CreditmemoInterface|Creditmemo $memo
     * @return array
     * @see \Scommerce\GlobalSiteTag\Model\Session\BackendSession::getRefundData()
     */
    private function makeRefundDataItems($memo)
    {
        $items = [];
        foreach ($memo->getAllItems() as $memoItem) {
            try {
                $items[] = $this->makeItem($memoItem);
            } catch (\Exception $e) {
                // Do nothing
            }
        }
        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo\Item|\Magento\Sales\Api\Data\CreditmemoItemInterface $item
     * @return array
     * @throws \Exception
     * @see \Scommerce\GlobalSiteTag\Model\Session\BackendSession::getRefundData()
     */
    private function makeItem($item)
    {
        $product = $this->productRepository->getById($item->getProductId());
        if (!($category = $item->getOrderItem()->getGoogleCategory())) {
            $category = $this->helper->getProductCategoryName($product);
        }
        return [
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'brand' => $this->helper->getBrand($product),
            'category' => $category,
            'variant' => $this->helper->getProductVariant($product, $item),
            'price' => ($this->helper->sendBaseData() ? $item->getBasePrice() : $item->getPrice()),
            'quantity' => $item->getQty(),
        ];
    }
}
