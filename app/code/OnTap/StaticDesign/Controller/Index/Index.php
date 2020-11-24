<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\StaticDesign\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var ResultPageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param ResultPageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        ResultPageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
