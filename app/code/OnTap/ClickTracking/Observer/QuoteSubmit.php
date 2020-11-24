<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ClickTracking\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use OnTap\ClickTracking\Model\Session;
use OnTap\ClickTracking\Model\Tracking;

class QuoteSubmit implements ObserverInterface
{
    /**
     * @var Session
     */
    protected Session $session;

    /**
     * QuoteSubmitSuccess constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->session->isSessionExists()) {
            /** @var Order $order */
            $order = $observer->getData('order');
            $order->setData(Tracking::GCLID, $this->session->getTrackingValue(Tracking::GCLID));
            $order->setData(Tracking::MSCLKID, $this->session->getTrackingValue(Tracking::MSCLKID));
            $order->setData(Tracking::ITO, $this->session->getTrackingValue(Tracking::ITO));
        }
    }
}
