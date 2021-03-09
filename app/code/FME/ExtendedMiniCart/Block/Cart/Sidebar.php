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
namespace FME\ExtendedMiniCart\Block\Cart;

use Magento\Framework\View\Element\Template;

/**
 * Class Sidebar
 *
 */
class Sidebar extends Template
{
   /**
    * Sidebar constructor.
    * @param Template\Context $context
    * @param array $data
    */
   public function __construct(
       Template\Context $context,
       \FME\ExtendedMiniCart\Helper\Data $helper,
       array $data = []
   ) {
       parent::__construct($context, $data);
       $this->helper=$helper;
   }
  /**
    * @return int
    * 
    */
   public function getConfigForExtendedMiniCart(){
    return $this->helper->showDefault();
   }
  /**
    * @return int
    * 
    */
   public function getConfigShowScroll(){
    return $this->helper->showScroll();
   }
}