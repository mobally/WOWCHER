<?php
/**
 * Scommerce Global Site Tag block
 *
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GlobalSiteTag\Block;

class Gtag extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Scommerce\GlobalSiteTag\Helper\Data
     */
    protected $_gtagData;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_salesFactory;
	
	/**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
	protected $_coreSession;
	
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Scommerce\GlobalSiteTag\Helper\Data $gtagData
	 * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order $salesOrderFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Scommerce\GlobalSiteTag\Helper\Data $gtagData,
	    \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $salesOrderFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->_gtagData = $gtagData;
        $this->_checkoutSession = $checkoutSession;
        $this->_salesFactory = $salesOrderFactory;
        $this->_request = $request;
		$this->_coreSession = $context->getSession();
        parent::__construct($context, $data);
    }

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName()
    {
        if (!$this->hasData('page_name')) {
            $this->setPageName($this->escapeJsQuote($_SERVER['REQUEST_URI']));
        }
        return $this->getData('page_name');
    }

    /**
     * Retrieve domain url without www or subdomain
     *
     * @return string
     */
    public function getMainDomain()
    {
        if (!$this->hasData('main_domain')) {
            $host = $this->_request->getHttpHost();
            if (substr_count($host,'.')>1 && (!$this->getHelper()->isDomainAuto())){
                $this->setMainDomain(substr($host,strpos($host,'.')+1));
            }
            else{
                $this->setMainDomain('auto');
            }
        }
        return $this->getData('main_domain');
    }

    /**
     * Render block html if Global Site Tag (gtag.js) is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getHelper()->isEnabled() ? parent::_toHtml() : '';
    }

    /**
     * @return \Scommerce\GlobalSiteTag\Helper\Data
     */
    public function getHelper()
    {
        return $this->_gtagData;
    }

    /**
     * Retrieve current order
     *
     * @return \Magento\Sales\Model\Order\OrderFactory
     */
    public function getOrder()
    {
        $orderId = $this->_checkoutSession->getLastOrderId();
        return $this->_salesFactory->load($orderId);
    }

    /**
     * Return if it is order confirmation page or not
     *
     * @return boolean
     */
    public function isEcommerce()
    {
        if ((strpos($this->getPageName(), 'success')!==false) && (strpos($this->getPageName(), 'checkout')!==false)){
            return true;
        }
        return false;
    }
}