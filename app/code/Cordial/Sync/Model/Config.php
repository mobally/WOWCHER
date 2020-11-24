<?php

namespace Cordial\Sync\Model;

use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Config
{
    const MODULE_NAME = 'Cordial_Sync';

    //= General Settings

    private $allStoreIds = [0 => null, 1 => null];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConfig
     */
    private $resourceConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var DateTimeFactory
     */
    private $datetimeFactory;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @method __construct
     * @param  StoreManagerInterface    $storeManager
     * @param  ScopeConfigInterface     $scopeConfig
     * @param  ResourceConfig           $resourceConfig
     * @param  EncryptorInterface       $encryptor
     * @param  DateTimeFactory          $datetimeFactory
     * @param  ModuleListInterface      $moduleList
     * @param  ProductMetadataInterface $productMetadata
     * @param  LoggerInterface          $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ResourceConfig $resourceConfig,
        EncryptorInterface $encryptor,
        DateTimeFactory $datetimeFactory,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->encryptor = $encryptor;
        $this->datetimeFactory = $datetimeFactory;
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
        $this->logger = $logger;
    }

    /**
     * @method getStoreManager
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return mixed
     */
    public function getConfig($configPath, $scopeId = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($configPath, $scope ?: ScopeInterface::SCOPE_STORE, $scopeId ?: $this->storeManager->getStore()->getId());
    }


    public function getModuleVersion()
    {
        return $this->moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }

    public function getMagentoPlatformName()
    {
        return $this->productMetadata->getName();
    }

    public function getMagentoPlatformEdition()
    {
        return $this->productMetadata->getEdition();
    }

    public function getMagentoPlatformVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
