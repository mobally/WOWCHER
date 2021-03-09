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
namespace FME\ExtendedMiniCart\Plugin\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
/**
 * Class Cart
 *
 */
class Cart {
   /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     * @return object
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $data = $result;
        return $data;
    }
}