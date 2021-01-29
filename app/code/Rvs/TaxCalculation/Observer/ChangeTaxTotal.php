<?php
namespace Rvs\TaxCalculation\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;

class ChangeTaxTotal implements ObserverInterface
{
    protected $_checkoutSession;
    public function __construct(\Magento\Checkout\Model\Session $_checkoutSession)
    {
        $this->_checkoutSession = $_checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
 	$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
	$currentStore = $storeManager->getStore();
	$storeID = $currentStore->getStoreId();
	if ($storeID == 4)
        {
            $tax_rate = 21;
        }
        if ($storeID == 2)
        {
            $tax_rate = 23;
        }
        if ($storeID == 3)
        {
            $tax_rate = 21;
        }
        if ($storeID == 1)
        {
            $tax_rate = 21;
        }
        

        $cartData = $observer->getQuote()
            ->getAllVisibleItems();
        $cartDataCount = count($cartData);
        /** @var Magento\Quote\Model\Quote\Address\Total */
        $total = $observer->getData('total');

        // $subtotal = $total['subtotal'];
        $item_price_tax = 0;
        $merchant_email = array();
        $row_total_price_incl_tax_same_merchant = 0;
        $row_total_price_same_merchant = 0;
        foreach ($cartData as $item)
        {
            if ($item->getProduct()
                ->getData('merchant_email') != '')
            {
                $merchant_email[] = $item->getProduct()
                    ->getData('merchant_email');
            }
        }
        $i = 1;
        foreach ($cartData as $item)
        {
            $row_total_price = $item->getRowTotal();
            $row_total_price_incl_tax = $item->getRowTotalInclTax();
            $merchant_email_loop = $merchant_email;
            $merchantEmailc = "";
            if ($item->getProduct()
                ->getData('merchant_email') != '')
            {
                $currentmerchant_email = $item->getProduct()
                    ->getData('merchant_email');
                $merchant_emailcount = array_count_values($merchant_email_loop);
                $merchantEmailc = $merchant_emailcount[$currentmerchant_email];
            }

            if ($merchantEmailc > 1)
            {
                $row_total_price_incl_tax_same_merchant += $row_total_price_incl_tax;
                $row_total_price_same_merchant += $row_total_price;
                if ($merchantEmailc == $i && $row_total_price_incl_tax_same_merchant > 22)
                {
                    $item_price_tax += $row_total_price_same_merchant * $tax_rate / 100;
                }

            }
            else
            {
                if ($row_total_price_incl_tax > 22)
                {
                    $item_price_tax += $row_total_price * $tax_rate / 100;
                }
            }
            $i++;
        }
        $total->setTotalAmount('tax', $item_price_tax);

        return $this;
    }
}


