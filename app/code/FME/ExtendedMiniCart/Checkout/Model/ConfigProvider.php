<?php
/**
* FME Extensions
*
* NOTICE OF LICENSE 
*
* This source file is subject to the fmeextensions.com license that is
* available through the world-wide-web at this URL:
* https://www.fmeextensions.com/LICENSE.txt
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this extension to newer
* version in the future.
*
* @category FME
* @package FME_ExtendedMiniCart
* @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
* @license https://fmeextensions.com/LICENSE.txt
*/
namespace FME\ExtendedMiniCart\Checkout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 *
 */
class ConfigProvider implements ConfigProviderInterface
{
  /**
    * ConfigProvider constructor.
    * @param Template\Context $context
    * @param array $data
    */
   public function __construct(
       \FME\ExtendedMiniCart\Helper\Data $helper,
       \Magento\Directory\Model\Currency $currency
   ) {
      $this->helper=$helper;
      $this->_currency = $currency;
   }
  /**
    * @return int
    * 
    */
    public function getConfig()
    {
        $config = [];
        $config['myCustomData'] = $this->helper->showDefault();
        $config['showCustomQtyUpdate'] = $this->helper->showQtyUpdate();
        $config['showCurrencySymbol'] = $this->_currency->getCurrencySymbol();
        return $config;
    }
}
