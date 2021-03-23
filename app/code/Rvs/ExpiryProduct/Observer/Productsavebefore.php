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
	$flag = 0;
	foreach($associatedProducts as $value){
	$status = $value->getStatus();
	if($status == 1)
	$flag = 1;
	//break;
	}
	
	if($flag == 0 && $pro_status == 1 && $type_id == 'grouped'){
        throw new \Magento\Framework\Exception\LocalizedException(__('Please make sure at least one of the Child/Product ID is enabled, Enable and try again.'));
        
	}
    }   
}
