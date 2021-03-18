<?php
namespace Rvs\ExpiryProduct\Model\ResourceModel\Data;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
        'Rvs\ExpiryProduct\Model\Data',
        'Rvs\ExpiryProduct\Model\ResourceModel\Data'
    );
    }
}
