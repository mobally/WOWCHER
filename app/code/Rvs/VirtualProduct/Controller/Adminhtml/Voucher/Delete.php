<?php

namespace Rvs\VirtualProduct\Controller\Adminhtml\Voucher;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Delete extends \Rvs\VirtualProduct\Controller\Adminhtml\Voucher implements HttpGetActionInterface
{
    /**
     * URL rewrite delete action
     *
     * @return void
     */
    public function execute()
    {
        echo "delete process"; die;
        if ($this->_getVoucher()->getId()) {
            try {
                $this->_getVoucher()->delete();
                $this->messageManager->addSuccess(__('You deleted the Voucher.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t delete Voucher right now.'));
                $this->_redirect('*/*/edit/', ['voucher_id' => $this->_getVoucher()->getId()]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }
}
