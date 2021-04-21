<?php

namespace Rvs\VirtualProduct\Model\ResourceModel\Voucher;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'voucher_id';
    
    protected function _construct()
    {
        $this->_init(
            \Rvs\VirtualProduct\Model\Voucher::class,
            \Rvs\VirtualProduct\Model\ResourceModel\Voucher::class
        );
    }
}
