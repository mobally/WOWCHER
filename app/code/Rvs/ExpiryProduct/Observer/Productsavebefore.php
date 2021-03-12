<?php

namespace Rvs\ExpiryProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsavebefore implements ObserverInterface
{    

	protected $_request;

	public function __construct(
	    \Magento\Framework\App\RequestInterface $request
	) {
	    $this->_request = $request;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    
   
	    $_product = $observer->getProduct();
	    $data = $this->_request->getPost();
	    $type_id = $observer->getProduct()->getOrigData('type_id');
	    $pro_status = $data['product']['status'];
        $status = $_product->getStatus();
	$associatedProducts = $_product->getTypeInstance()->getAssociatedProducts($_product);
	$flag = false;
	foreach($associatedProducts as $value){
	$status = $value->getStatus();
	if($status == 1)
	$flag = true;
	break;
	}
	
	if(!$flag && $pro_status == 1 && $type_id == 'grouped'){
        throw new \Magento\Framework\Exception\LocalizedException(__('Please make sure at least one of the Child/Product ID is enabled, Enable and try again.'));
        
	}
    }   
}
