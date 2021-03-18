<?php

namespace Rvs\ExpiryProduct\Model\ResourceModel;


use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Data extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('business_data', 'id'); //id is a primary key 
    }
}
