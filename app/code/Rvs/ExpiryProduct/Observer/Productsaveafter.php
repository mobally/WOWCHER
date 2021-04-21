<?php

namespace Rvs\ExpiryProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsaveafter implements ObserverInterface
{    

 protected $_request;

	public function __construct(
	    \Magento\Framework\App\RequestInterface $request
	) {
	    $this->_request = $request;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getEvent()->getProduct();
        $data = $this->_request->getPost();
	    $type_id = $observer->getProduct()->getOrigData('type_id');
	    $pro_status = $data['product']['status'];
	    
	    $json_productdisplay_row = $data['product']['productdisplay_row'];
	    $json_productdisplay_column = $data['product']['productdisplay_column'];
	    $result_row = json_decode($json_productdisplay_row);
	   $result_column = json_decode($json_productdisplay_column);
	   if($result_row == '' && $type_id == 'grouped'){
        throw new \Magento\Framework\Exception\LocalizedException(__('Fix error before enabling this product - JSON format is invalid in productDisplay.row attribute'));
  	}
	   
	   if($result_column == '' && $type_id == 'grouped'){
        throw new \Magento\Framework\Exception\LocalizedException(__('Fix error before enabling this product - JSON format is invalid in productdisplay_column'));
  	}
        $status = $_product->getStatus();
        $deal_status = $_product->getDealStatus();
        $countdown_timer = $_product->getCountdownTimer();
        if($status == 2){
	$_product->setDealStatus(12);
	$_product->save();
	}
	if($deal_status == 11 && $countdown_timer == ''){
	$time_new = mt_rand(0, 23) . ":" . str_pad(mt_rand(0, 59) , 2, "0", STR_PAD_LEFT) . ':59 ';
	$NewDate = Date('Y-m-d ' . $time_new, strtotime('+2 days'));
        $countdown_timer = strtotime($NewDate) . '000';
        $_product->setCountdownTimer($countdown_timer);
	$_product->setDealStatus(12);
	$_product->save();
	}
    }   
}
