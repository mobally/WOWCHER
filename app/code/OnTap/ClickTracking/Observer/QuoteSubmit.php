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
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

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
     /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    public function __construct(
        Session $session,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ){
        $this->session = $session;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;

    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
       // if ($this->session->isSessionExists()) {
            /** @var Order $order */
            $order = $observer->getData('order');
            $gclids = $this->cookieManager->getCookie('gclidnew');
            $msclkid = $this->cookieManager->getCookie('msclkidnew');
            $ito = $this->cookieManager->getCookie('itonew'); 
            $order->setData(Tracking::GCLID, $gclids);
            $order->setData(Tracking::MSCLKID, $msclkid);
            $order->setData(Tracking::ITO, $ito);
       // }
    }
}
