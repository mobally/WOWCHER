<?php
namespace Rvs\ExpiryProduct\Model;

use Magento\Framework\Model\AbstractModel;

    class Data extends AbstractModel
    {   
        protected function _construct()
        {
            $this->_init('Rvs\ExpiryProduct\Model\ResourceModel\Data');
            
        }
    }
