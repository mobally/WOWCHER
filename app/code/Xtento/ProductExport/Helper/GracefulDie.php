<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            nH0RbGUjtjA7eYYFruny569maB8neebht0E+W5DEN/g=
 * Last Modified: 2021-01-19T19:26:20+00:00
 * File:          app/code/Xtento/ProductExport/Helper/GracefulDie.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Helper;

use Xtento\ProductExport\Model\Log;

class GracefulDie
{
    protected static $isInitialized = false;
    protected static $isEnabled = false;

    public static function enable()
    {
        return; // Disabled, catched other error messages too. Only enabling for debugging.
        self::$isEnabled = true;
        if (!self::$isInitialized) {
            register_shutdown_function(['\Xtento\ProductExport\Helper\GracefulDie', 'beforeDieFromShutdown']); // Fatal error or similar
            self::$isInitialized = true;
        }
    }

    public static function disable()
    {
        self::$isEnabled = false;
    }

    /**
     * @param null $message
     * @param bool $exit
     */
    public static function beforeDie($message = null, $exit = false)
    {
        if (self::$isEnabled) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $registry = $objectManager->get('\Magento\Framework\Registry');
            $logEntry = $registry->registry('productexport_log');
            if ($logEntry && $logEntry->getId()) {
                if (strstr($message, 'should always be of the type int since Symfony') !== false) {
                    return; // Ignore
                }
                $logEntry->setResult(Log::RESULT_FAILED);
                $logEntry->addResultMessage($message);
                $logEntry->setResultMessage($logEntry->getResultMessages());
                $logEntry->save();
                if (strlen($message) > 16) {
                    // No empty error message
                    $objectManager->get('\Xtento\ProductExport\Model\Export')->setLogEntry($logEntry)->errorEmailNotification();
                }
            }
        }
    }

    public static function beforeDieFromShutdown()
    {
        $message = 'Shutdown/Crash: ' . print_r(error_get_last(), true);
        //'Stack Trace: ' . PHP_EOL . (new \Exception())->__toString();

        self::beforeDie($message, false);
    }
}