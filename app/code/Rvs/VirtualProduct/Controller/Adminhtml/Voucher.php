<?php

namespace Rvs\VirtualProduct\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Voucher extends Action
{    
    const ADMIN_RESOURCE = 'Rvs_VirtualProduct::voucher';

    protected $_voucher;
    
    protected function _getVoucher()
    {
        if (!$this->_voucher) {
            $this->_voucher = $this->_objectManager->create(\Rvs\VirtualProduct\Model\Voucher::class);
            $voucherId = (int)$this->getRequest()->getParam('voucher_id', 0);
            if ($voucherId) {
                $this->_voucher->load($voucherId);
            }
        }

        return $this->_voucher;
    }
}
