<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ProductImport\Logger;

use Monolog\Logger;

class LogFile extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/dealfeed.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
