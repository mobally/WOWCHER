<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\Timer\Observer\Checkout\Controller\Cart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

class IndexObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected Session $session;

    /**
     * IndexObserver constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($observer->getFullActionName() == 'checkout_cart_index') {
            if ($this->session->hasQuote() && $this->session->getQuote()->hasItems()) {
                return;
            }

            /** @var \Magento\Framework\View\LayoutInterface $layout */
            $layout = $observer->getLayout();
            $layout->getUpdate()->addHandle('checkout_cart_index_cart_empty');
        }
    }
}
