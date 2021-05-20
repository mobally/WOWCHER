<?php
namespace Awin\AdvertiserTracking\Controller\Settings;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultJsonFactory;
    protected $_helper;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Awin\AdvertiserTracking\Helper\Data $helper
    )
    {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $response = ['advertiserId' => $this->_helper->getAdvertiserId()];
        return $resultJson->setData($response);
    }
}