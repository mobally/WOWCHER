<?php
namespace Awin\AdvertiserTracking\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_request;
    protected $_cookieHandler;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Action\Context $context,
        \Awin\AdvertiserTracking\Cookie\CookieHandler $cookieHandler,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    )
    {
        $this->_request = $request;
        $this->_cookieHandler = $cookieHandler;
        $this->_pageFactory = $pageFactory;

        return parent::__construct($context);
    }

    public function execute()
    {
        $awc_from_url = $this->_request->getParam('awc');
        $source_from_url = $this->_request->getParam('source');

        if(strlen($awc_from_url)> 0)
        {
            $this->_cookieHandler->set("adv_awc", $awc_from_url);
        }

        if(strlen($source_from_url)> 0)
        {
            $this->_cookieHandler->set("source", $source_from_url);
        }
    }
}