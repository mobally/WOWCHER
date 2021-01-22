<?php

namespace Rvs\ExpiryProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsaveafter implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getEvent()->getProduct();
        $status = $_product->getStatus();
	if($status == 2){
	$_product->setDealStatus(12);
	$_product->save();
	}
    }   
}
