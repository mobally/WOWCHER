<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ClickTracking\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use OnTap\ClickTracking\Model\Session;
use OnTap\ClickTracking\Model\Tracking;
use Mageplaza\GeoIP\Helper\Address as HelperData;

class SubscriberSave implements ObserverInterface
{
    /**
     * @var Session
     */
    protected Session $session;

    /**
     * SubscriberSave constructor.
     * @param Session $session
     */
    public function __construct(
        Session $session,
        HelperData $helperData
    ) {
        $this->session = $session;
        $this->_helperData = $helperData;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->session->isSessionExists()) {
            /** @var Subscriber $subscriber */
            $subscriber = $observer->getData('subscriber');
            $subscriber->setData(Tracking::GCLID, $this->session->getTrackingValue(Tracking::GCLID));
            $subscriber->setData(Tracking::MSCLKID, $this->session->getTrackingValue(Tracking::MSCLKID));
            $subscriber->setData(Tracking::ITO, $this->session->getTrackingValue(Tracking::ITO));
            
        }
        
	$cust_info = $this->_helperData->getGeoIpData();
	$time_zone = $cust_info['timezone'];

	date_default_timezone_set($time_zone);
	 $timecurrent = date('d/m/Y, H:i:s');
        $subscriber->setLocalTime($timecurrent);
    }
}
