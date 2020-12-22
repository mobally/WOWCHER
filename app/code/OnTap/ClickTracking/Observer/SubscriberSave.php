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
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Mageplaza\GeoIP\Helper\Address as HelperData;

class SubscriberSave implements ObserverInterface
{
    /**
     * @var Session
     */
    protected Session $session;

     /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var $scopeConfigInterface
     */
    private $scopeConfigInterface;
    /**
     * SubscriberSave constructor.
     * @param Session $session
     */
    public function __construct(
        Session $session,
        HelperData $helperData,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->session = $session;
        $this->_helperData = $helperData;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
    $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);
 
        $this->cookieManager->setPublicCookie('wowcher-win','subscribed',$publicCookieMetadata);
    
            $gclids = $this->cookieManager->getCookie('gclidnew');
            $msclkid = $this->cookieManager->getCookie('msclkidnew');
            $ito = $this->cookieManager->getCookie('itonew');           
            $subscriber = $observer->getData('subscriber');
         
            $subscriber->setData(Tracking::GCLID, $gclids);
            $subscriber->setData(Tracking::MSCLKID, $msclkid);
            $subscriber->setData(Tracking::ITO, $ito);
        
	$cust_info = $this->_helperData->getGeoIpData();
	$time_zone = $cust_info['timezone'];

	date_default_timezone_set($time_zone);
	 $timecurrent = date('Y-m-d, H:i:s');
        $subscriber->setLocalTime($timecurrent);
    }
}
