<?php

namespace Rvs\TaxCalculation\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;

class ChangeTaxTotal implements ObserverInterface
{
   protected $_checkoutSession;
   public function __construct (
    \Magento\Checkout\Model\Session $_checkoutSession
    ) {
    $this->_checkoutSession = $_checkoutSession;
}
   
    public function execute(Observer $observer)
    {
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
$storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$storeID       = $storeManager->getStore()->getStoreId(); 
			if($storeID = 4){
					$tax_rate = 21;
				}
				if($storeID = 2){
					$tax_rate = 23;
				}
if($storeID = 3){
					$tax_rate = 21;
				}if($storeID = 1){
					$tax_rate = 20;
				}

        $cartData = $observer->getQuote()->getAllVisibleItems();
  $cartDataCount = count( $cartData );
        /** @var Magento\Quote\Model\Quote\Address\Total */
        $total = $observer->getData('total');
        
       // $subtotal = $total['subtotal'];
	   $item_price_tax = 0;
        foreach( $cartData as $item ){
			$row_total_price = $item->getRowTotal();
			$row_total_price_incl_tax = $item->getRowTotalInclTax();
			
			if($row_total_price_incl_tax > 22){
									
				$item_price_tax += $row_total_price * $tax_rate/100;
			}
			
		}
            $total->setTotalAmount('tax', $item_price_tax);
       
        return $this;
    }
}