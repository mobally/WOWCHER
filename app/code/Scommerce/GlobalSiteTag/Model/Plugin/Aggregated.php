<?php

namespace Scommerce\GlobalSiteTag\Model\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\RequireJs\Config\File\Collector\Aggregated as RequireJsCollector;

/**
 * Remove RequireJS files if module is disabled
 *
 * Class Aggregated
 * @package Scommerce\GlobalSiteTag\Model\Plugin
 */
class Aggregated
{
    const CONFIG_DISABLE_GTAG = 'globalsitetag/general/active';

    /**
     * @var ScopeConfig
     */
    protected $scopeConfig;

    /**
     * Aggregated constructor.
     * @param ScopeConfig $scopeConfig
     */
    public function __construct(ScopeConfig $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param RequireJsCollector $subject
     * @param array $files
     * @return array
     */
    public function afterGetFiles(RequireJsCollector $subject, array $files)
    {
        $disabled = $this->scopeConfig->getValue(
            self::CONFIG_DISABLE_GTAG,
            ScopeInterface::SCOPE_STORE
        );

        if ($disabled) {
            return $files;
        }

        foreach ($files as $k => $v) {
            if ($v->getModule() === 'Scommerce_GlobalSiteTag') {
                unset($files[$k]);
            }
        }

        return $files;
    }
}