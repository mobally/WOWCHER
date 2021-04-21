<?php

namespace Rvs\VirtualProduct\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    protected $logger;

    public function __construct(
        Context $context,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;        
        parent::__construct($context);
    }
    
    public function execute()
    {
        $virtulObj = $this->_objectManager->create(\Rvs\VirtualProduct\Cron\ProcessEmail::class);
        $virtulObj->execute();        
    }    
}
