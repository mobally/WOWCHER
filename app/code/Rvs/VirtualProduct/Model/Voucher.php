<?php

namespace Rvs\VirtualProduct\Model;

class Voucher extends \Magento\Framework\Model\AbstractModel
{
    const VOUCHER_ID = 'voucher_id';
    
    protected function _construct()
    {
        $this->_init(\Rvs\VirtualProduct\Model\ResourceModel\Voucher::class);
    }
    
    public function getVoucherId()
    {
        return $this->getData(self::VOUCHER_ID);
    }
    
    public function setVoucherId($voucherId)
    {
        return $this->setData(self::VOUCHER_ID, $voucherId);
    }
}
