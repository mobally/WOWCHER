<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ClickTracking\Controller\Track;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use OnTap\ClickTracking\Model\Session;
use OnTap\ClickTracking\Model\Tracking;

class Index implements HttpGetActionInterface
{
    /**
     * @var RawFactory
     */
    protected RawFactory $rawPageFactory;

    /**
     * @var Session
     */
    protected Session $session;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * Index constructor.
     * @param RawFactory $rawFactory
     * @param RequestInterface $request
     * @param Session $session
     */
    public function __construct(
        RawFactory $rawFactory,
        RequestInterface $request,
        Session $session
    ) {
        $this->rawPageFactory = $rawFactory;
        $this->session = $session;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
       // $this->session->setTrackingValue(Tracking::GCLID, $this->request->getParam(Tracking::GCLID));
      //  $this->session->setTrackingValue(Tracking::MSCLKID, $this->request->getParam(Tracking::MSCLKID));
     //   $this->session->setTrackingValue(Tracking::ITO, $this->request->getParam(Tracking::ITO));

        $page = $this->rawPageFactory->create();
        $page->setHttpResponseCode(200);
        $page->setContents('');

        return $page;
    }
}
