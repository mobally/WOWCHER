<?php
/**
 * Copyright © 2015 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Scommerce\GlobalSiteTag\Block\Checkout;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Order Confirmation Block
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /** @var \Scommerce\GlobalSiteTag\Helper\Data */
    private $helper;

    /** @var \Magento\Checkout\Model\Session */
    private $checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getOrderData()
    {
        $order = $this->getOrder();
        $orderData = [];
        if ($this->helper->isEnhancedEcommerceEnabled()) {
            $orderData = [
                'transaction_id' => $this->getTransactionId($order),
                'value' => $order->getGrandTotal(),
                'currency' => ($this->helper->sendBaseData()) ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode(),
                'tax' => ($this->helper->sendBaseData()) ? $order->getBaseTaxAmount() : $order->getTaxAmount(),
                'shipping' => ($this->helper->sendBaseData()) ? $order->getBaseShippingAmount() : $order->getBaseShippingAmount(),
                'items' => $this->getOrderItems($order),
                'coupon' => $order->getCouponCode()
            ];
        }

        if ($this->helper->isDynamicRemarketingEnabled()) {
            if ($this->helper->isOtherSiteEnabled()) {
                $orderData['dynx_totalvalue'] = ($this->helper->sendBaseData()) ? $order->getBaseGrandTotal() : $order->getGrandTotal();
                $orderData['dynx_itemid'] = $this->getOrderProductsSKUs($order);
                $orderData['dynx_pagetype'] = 'purchase';
            } else {
                $orderData['ecomm_totalvalue'] = ($this->helper->sendBaseData()) ? $order->getBaseGrandTotal() : $order->getGrandTotal();
                $orderData['ecomm_prodid'] = $this->getOrderProductsSKUs($order);
                $orderData['ecomm_pagetype'] = 'purchase';
            }
        }

        return $orderData;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getOrderProductsSKUs($order)
    {
        $skus = [];
        /** @var \Magento\Sales\Api\Data\OrderItemInterface[]|\Magento\Sales\Model\Order\Item[] $orderItems */
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $product = $item->getProduct();
            if (!$product) {
                continue;
            }
            $skus[] = $product->getSku();
        }
        return $skus;
    }

    /**
     * Render block html if google universal analytics is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->helper->isEnabled() ? parent::_toHtml() : '';
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
     * @param Order $order
     * @return array
     * @throws \Exception
     */
    private function getOrderItems($order)
    {
        $items = [];
        /** @var \Magento\Sales\Api\Data\OrderItemInterface[]|\Magento\Sales\Model\Order\Item[] $orderItems */
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $product = $item->getProduct();
            if (!$product) {
                continue;
            }
            $items[] = [
                'id' => $product->getSku(),
                'name' => $product->getName(),
                'brand' => $this->helper->getBrand($product),
                'category' => $this->helper->getOrderGoogleCategoryName($item),
                'variant' => $this->helper->getProductVariant($product, $item),
                'price' => ($this->helper->sendBaseData() ? $item->getBasePrice() : $item->getPrice()),
                'quantity' => $item->getQtyOrdered(),
            ];
        }
        return $items;
    }

    /**
     * @return Order
     */
    private function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }
}
