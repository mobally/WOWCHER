<?php

namespace Scommerce\GlobalSiteTag\Controller\Index;

class Removefromcart extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
	protected $_coreSession;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Session\SessionManagerInterface $coresession
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\SessionManagerInterface $coresession,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->_coreSession = $coresession;
		parent::__construct($context);
    }

    /**
     * return add to basket product data
     *
     *
     * @return string
     */
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->_coreSession->getProductOutBasket());
        return $resultRaw;
    }

}
