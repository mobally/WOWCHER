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
namespace FME\ExtendedMiniCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 */
class Data extends AbstractHelper
{
    /**
     * default Config Path
     */
    const XML_CONFIG_UPDATE_DEFAULTS = 'extendedminicart/globalsetting/enable_extendedminicart';
    const XML_CONFIG_UPDATE_SUMMARY = 'extendedminicart/globalsetting/enable_minicartsummary';
    const XML_CONFIG_UPDATE_RELEATED = 'extendedminicart/globalsetting/enable_minicartreleated';
    const XML_CONFIG_UPDATE_QTY = 'extendedminicart/globalsetting/enable_minicartqtyupdate';
    const XML_CONFIG_UPDATE_SCROLL = 'extendedminicart/globalsetting/enable_minicartautoscrol';
    /**
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Group $customerGroupCollection
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     *
     * @return void
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Group $customerGroupCollection,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->store = $store;
        $this->_customerSession = $customerSession;
        $this->_customerGroupCollection = $customerGroupCollection;
        $this->_productRepository = $productRepository;
        parent::__construct(
            $context
        );
    }
    /**
     * Retrieve System Config
     * @return int
     */
    public function showDefault(){
        return $this->_getConfig(self::XML_CONFIG_UPDATE_DEFAULTS);
    }
    /**
     * Retrieve System Config
     * @return int
     */
    public function showSummary(){
        return $this->_getConfig(self::XML_CONFIG_UPDATE_SUMMARY);
    }
    /**
     * Retrieve System Config
     * @return int
     */
    public function showReleated(){
        return $this->_getConfig(self::XML_CONFIG_UPDATE_RELEATED);
    }
    /**
     * Retrieve System Config
     * @return int
     */
    public function showQtyUpdate(){
        return $this->_getConfig(self::XML_CONFIG_UPDATE_QTY);
    }
    /**
     * Retrieve System Config
     * @return int
     */
    public function showScroll(){
        return $this->_getConfig(self::XML_CONFIG_UPDATE_SCROLL);
    }
    /**
     * Retrieve base url
     * @return string
     */
    public function getBaseUrl(){
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    /**
     * @param $path
     * @return int
     */
    protected function _getConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
