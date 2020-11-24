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
     * CustomerRegistered constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $session
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $session
    ) {
        $this->customerRepository = $customerRepository;
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var CustomerInterface $customer */
        $customer = $observer->getData('customer');

        $customer->setCustomAttribute(Tracking::GCLID, $this->session->getTrackingValue(Tracking::GCLID));
        $customer->setCustomAttribute(Tracking::MSCLKID, $this->session->getTrackingValue(Tracking::MSCLKID));
        $customer->setCustomAttribute(Tracking::ITO, $this->session->getTrackingValue(Tracking::ITO));
        $this->customerRepository->save($customer);
    }
}
