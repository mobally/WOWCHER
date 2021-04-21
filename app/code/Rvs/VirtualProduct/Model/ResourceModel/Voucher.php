<?php

namespace Rvs\VirtualProduct\Model\ResourceModel;

class Voucher extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('rvs_voucher_list', 'voucher_id');
    }
}
