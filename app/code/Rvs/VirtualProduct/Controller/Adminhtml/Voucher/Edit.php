<?php

namespace Rvs\VirtualProduct\Controller\Adminhtml\Voucher;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Edit extends \Rvs\VirtualProduct\Controller\Adminhtml\Voucher implements HttpGetActionInterface
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Rvs_VirtualProduct::voucher');
        $editBlock = $this->_view->getLayout()->createBlock(\Rvs\VirtualProduct\Block\Adminhtml\Voucher\Edit::class,'',['data' => ['voucher' => $this->_getVoucher()]]);
        $this->_view->getPage()->getConfig()->getTitle()->prepend($editBlock->getHeaderText());
        $this->_addContent($editBlock);
        $this->_view->renderLayout();
    }
}
