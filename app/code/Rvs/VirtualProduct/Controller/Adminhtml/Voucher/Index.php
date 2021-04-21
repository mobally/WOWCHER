<?php

namespace Rvs\VirtualProduct\Controller\Adminhtml\Voucher;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Rvs\VirtualProduct\Controller\Adminhtml\Voucher implements HttpGetActionInterface
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Rvs_VirtualProduct::voucher');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Voucher Management'));
        $this->_view->renderLayout();
    }
}
