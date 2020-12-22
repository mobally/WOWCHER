<?php

namespace PHPCuong\CustomerRedirecting\Observer;

use Magento\Framework\Event\ObserverInterface;

class Customercookie implements ObserverInterface
{
    const CUSTOMER_GROUP_ID = 2;

    protected $_customerRepositoryInterface;
    
    private $cookieManager;
 protected $encryptor;
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->encryptor = $encryptor;
        }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);
 	$customerId = $customer->getId();
 	$encrypt =  $this->encryptor->encrypt($customerId);
        $this->cookieManager->setPublicCookie('wowcher-win','registered_user',$publicCookieMetadata);
        $this->cookieManager->setPublicCookie('dod_logged_in','standardUser',$publicCookieMetadata);
        $this->cookieManager->setPublicCookie('ct',$encrypt,$publicCookieMetadata);
        /*if ($customer->getGroupId() == 1) {
            $customer->setGroupId(self::CUSTOMER_GROUP_ID);
            $this->_customerRepositoryInterface->save($customer);;
        }*/
    }
}
