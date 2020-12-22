<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ClickTracking\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use OnTap\ClickTracking\Model\Session;
use OnTap\ClickTracking\Model\Tracking;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CustomerRegistered implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var Session
     */
    protected Session $session;

      /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * CustomerRegistered constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $session
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $session,        
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->session = $session;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var CustomerInterface $customer */
         /** @var Subscriber $subscriber */
        $gclids = $this->cookieManager->getCookie('gclidnew');
            $msclkid = $this->cookieManager->getCookie('msclkidnew');
            $ito = $this->cookieManager->getCookie('itonew'); 
        $customer = $observer->getData('customer');

        $customer->setCustomAttribute(Tracking::GCLID, $gclids);
        $customer->setCustomAttribute(Tracking::MSCLKID, $msclkid);
        $customer->setCustomAttribute(Tracking::ITO, $ito);
        $this->customerRepository->save($customer);
    }
}
