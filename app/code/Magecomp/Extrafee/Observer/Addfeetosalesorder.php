<?php
namespace Magecomp\Extrafee\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Addfeetosalesorder implements ObserverInterface
{

   protected $orderRepository;
     public function __construct(
         \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    )
    {
         $this->orderRepository = $orderRepository;
    
    }

    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getData('order');
        $ord_id = $order->getEntityId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orders = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($ord_id);
        $orderItems = $orders->getAllItems();
        $sum_duty_rates = 0;
        foreach($orderItems as $item) {
        $price = $item->getPrice();
        $pro_id = $item->getProductId();        
        $duty_rates = $item->getProduct()->getDutyRates();
        $itemqty = $item->getData('qty_ordered');
        $ware_house_deal = $item->getProduct()->getData('ware_house_deal');
        $deal_id_lsie = $item->getProduct()->getData('deal_id_lsie');
        $deal_id = $item->getProduct()->getData('deal_id');
        $item_price = $item->getData('base_row_total_incl_tax') * $itemqty;
               $item->setData('dutypaid', "P");
               $item->setData('fee', 0);
               $item->setData('deal_id', $deal_id);
               
               $item->save();
            }
            return $this;
        }

    	
    }
