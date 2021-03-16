<?php
namespace Rvs\ExpiryProduct\Observer;

class ConvertGuest implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Model\CustomerFactory $customer
    ) {
        $this->_storeManager = $storeManager;
        $this->orderCustomerService = $orderCustomerService;
        $this->_orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->_customer = $customer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();

        $orderId = $orderIds[0];
        $order = $this->_orderFactory->create()->load($orderId);

        $customer= $this->_customer->create();
        $customer->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());
        $customer->loadByEmail($order->getCustomerEmail());

        //Convert guest into customer
        if ($order->getId() && !$customer->getId()) {
            $this->orderCustomerService->create($orderId);
        } else {
            //if customer Registered and checkout as guest
            $order->setCustomerId($customer->getId());
            $order->setCustomerIsGuest(0);
            $this->orderRepository->save($order);
        }
    }
}
