<?php
/**
 * Cordial/Magento Integration RFP
 *
 * @category    Cordial
 * @package     Cordial_Sync
 * @author      Cordial Team <info@cordial.com>
 * @copyright   Cordial (http://cordial.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cordial\Sync\Block\Html;

class Loader extends \Magento\Framework\View\Element\Template
{

    const ACCOUNT_KEY_REPLACE = '$accountkey';

    /**
     * @var \Cordial\Sync\Helper\Config
     */
    protected $helper = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Cordial\Sync\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Cordial\Sync\Helper\Data $helper,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Is module Enabled
     *
     * @return bool
     */
    public function isEnable()
    {
        //$storeId
        return (bool)$this->helper->isEnabled();
    }

    /**
     * Returns JS Listener
     *
     * @return string
     */
    public function getJsLoader()
    {
        $jsCode = $this->helper->getJsLoader();
        $accountKey = $this->helper->getAccountKey();
        $jsCode = str_replace(self::ACCOUNT_KEY_REPLACE, $accountKey, $jsCode);
        return $jsCode;
    }

    /**
     * Returns JS Event
     *
     * @return string
     */
    public function getJsEvent()
    {
        return $this->helper->getJsEvent();
    }
}
