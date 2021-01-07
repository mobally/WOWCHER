<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GlobalSiteTag\Block\Adminhtml;

/**
 * Class Gtag
 * @package Scommerce\GlobalSiteTag\Block\Adminhtml
 */
class Gtag extends \Magento\Backend\Block\Template
{
    /** @var \Scommerce\GlobalSiteTag\Model\Session\BackendSession */
    private $session;

    /** @var \Scommerce\GlobalSiteTag\Helper\Data */
    private $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Scommerce\GlobalSiteTag\Model\Session\BackendSession $session
     * @param \Scommerce\GlobalSiteTag\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Scommerce\GlobalSiteTag\Model\Session\BackendSession $session,
        \Scommerce\GlobalSiteTag\Helper\Data $helper,
        array $data = []
    ) {
        $this->session = $session;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getRefundData()
    {
        return $this->session->getRefundData(true);
    }

    /**
     * @return bool
     */
    public function hasRefundData()
    {
        return $this->session->getRefundData() !== null;
    }

    /**
     * @return array
     */
    public function getOrderData()
    {
        return $this->session->getOrderData(true);
    }

    /**
     * @return bool
     */
    public function hasOrderData()
    {
        return $this->session->getOrderData() !== null;
    }

    /**
     * @return \Scommerce\GlobalSiteTag\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Show block only if module enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->helper->isEnabled() ? parent::_toHtml() : '';
    }
}
