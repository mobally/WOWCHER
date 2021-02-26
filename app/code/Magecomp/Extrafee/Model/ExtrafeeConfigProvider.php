<?php
namespace Magecomp\Extrafee\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Quote\Model\Quote;

class ExtrafeeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magecomp\Extrafee\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $taxHelper;

    /**
     * @param \Magecomp\Extrafee\Helper\Data $dataHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magecomp\Extrafee\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magecomp\Extrafee\Helper\Tax $helperTax

    )
    {
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->taxHelper = $helperTax;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $ExtrafeeConfig = [];
        $enabled = $this->dataHelper->isModuleEnabled();
        $minimumOrderAmount = $this->dataHelper->getMinimumOrderAmount();
        $ExtrafeeConfig['fee_label'] = $this->dataHelper->getFeeLabel();
        $quote = $this->checkoutSession->getQuote();
        $subtotal = $quote->getSubtotal();
 
        $items = $quote->getItems();
	$sum_duty_rates = 0;
/*	foreach($items as $item) {
	$pro_id = $item->getProductId();
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$product = $objectManager->get('Magento\Catalog\Model\Product')->load($pro_id);
	$duty_rates = $product->getDutyRates();
	$itemqty = $item->getQty();
	   $item_price = $item->getPriceInclTax() * $item->getQty();
	   $final_duty = $item_price * $duty_rates / 100;
	   $sum_duty_rates += $final_duty;
	}*/
	
	$item_price_tax = 0;
        $merchant_email = array();
        $row_total_price_incl_tax_same_merchant = 0;
        $row_total_price_same_merchant = 0;
        foreach ($items as $item)
        {
            if ($item->getProduct()
                ->getData('merchant_email') != '')
            {
                $merchant_email[] = $item->getProduct()
                    ->getData('merchant_email');
            }
        }
        $i = 1;
        foreach ($items as $item)
        {
            $row_total_price = $item->getRowTotal();
            $row_total_price_incl_tax = $item->getRowTotalInclTax();
            $merchant_email_loop = $merchant_email;
            $duty_rates = $item->getProduct()->getData('duty_rates');
            $ware_house_deal = $item->getProduct()->getData('ware_house_deal');
            //exit;
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
                if ($merchantEmailc == $i && $row_total_price_incl_tax_same_merchant > 150 && $ware_house_deal == 'yes')
                {
                    $sum_duty_rates += $row_total_price_incl_tax_same_merchant * $duty_rates / 100;
                }

            }
            else
            {
                if ($row_total_price_incl_tax > 150 && $ware_house_deal == 'yes')
                {
                    $sum_duty_rates += $row_total_price_incl_tax * $duty_rates / 100;
                }
            }
            $i++;
        }

        
        $ExtrafeeConfig['custom_fee_amount'] = $sum_duty_rates;
        if ($this->taxHelper->isTaxEnabled() && $this->taxHelper->displayInclTax()) {
            $address = $this->_getAddressFromQuote($quote);
            $ExtrafeeConfig['custom_fee_amount'] = $this->dataHelper->getExtrafee() + $address->getFeeTax();
        }
        if ($this->taxHelper->isTaxEnabled() && $this->taxHelper->displayBothTax()) {

            $address = $this->_getAddressFromQuote($quote);
            $ExtrafeeConfig['custom_fee_amount'] = $this->dataHelper->getExtrafee();
            $ExtrafeeConfig['custom_fee_amount_inc'] = $this->dataHelper->getExtrafee() + $address->getFeeTax();

        }
        $ExtrafeeConfig['displayInclTax'] = $this->taxHelper->displayInclTax();
        $ExtrafeeConfig['displayExclTax'] = $this->taxHelper->displayExclTax();
        $ExtrafeeConfig['displayBoth'] = $this->taxHelper->displayBothTax();
        $ExtrafeeConfig['exclTaxPostfix'] = __('Excl. Tax');
        $ExtrafeeConfig['inclTaxPostfix'] = __('Incl. Tax');
        $ExtrafeeConfig['TaxEnabled'] = $this->taxHelper->isTaxEnabled();
        $ExtrafeeConfig['show_hide_Extrafee_block'] = ($enabled && ($minimumOrderAmount <= $subtotal) && $quote->getFee()) ? true : false;
        $ExtrafeeConfig['show_hide_Extrafee_shipblock'] = ($enabled && ($minimumOrderAmount <= $subtotal)) ? true : false;
        return $ExtrafeeConfig;
    }

    protected function _getAddressFromQuote(Quote $quote)
    {
        return $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
    }
}

