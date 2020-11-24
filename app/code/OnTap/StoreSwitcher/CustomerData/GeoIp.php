<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\StoreSwitcher\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\GeoIP\Helper\Address;
use Magento\Framework\View\LayoutInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class GeoIp implements SectionSourceInterface
{
    const XML_ALLOW_COUNTRIES = 'general/geoip/allowed_country_ids';

    /**
     * @var Address
     */
    protected Address $addressHelper;

    /**
     * @var LayoutInterface
     */
    protected LayoutInterface $layout;

    /**
     * @var GetBlockByIdentifierInterface
     */
    protected GetBlockByIdentifierInterface $getBlockByIdentifier;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var PhpCookieManager
     */
    protected PhpCookieManager $cookieManager;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * GeoIp constructor.
     * @param Address $addressHelper
     * @param GetBlockByIdentifierInterface $getBlockByIdentifier
     * @param LayoutInterface $layout
     * @param LoggerInterface $logger
     * @param PhpCookieManager $cookieManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Address $addressHelper,
        GetBlockByIdentifierInterface $getBlockByIdentifier,
        LayoutInterface $layout,
        LoggerInterface $logger,
        PhpCookieManager $cookieManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->addressHelper = $addressHelper;
        $this->layout = $layout;
        $this->getBlockByIdentifier = $getBlockByIdentifier;
        $this->logger = $logger;
        $this->cookieManager = $cookieManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    protected function allowedCountryCodes(): array
    {
        $codesString = $this->scopeConfig->getValue(self::XML_ALLOW_COUNTRIES, ScopeInterface::SCOPE_STORE);
        $codes = explode(',', $codesString);
        $codes = array_map('trim', $codes);
        return array_map('strtolower', $codes);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getWebsiteCode(): string
    {
        return strtolower($this->storeManager->getWebsite()->getCode());
    }

    /**
     * @inheritDoc
     */
    public function getSectionData(): array
    {
        $forceful = $this->cookieManager->getCookie('forceGeoIpCountry');

        if (!isset($forceful)) {
            $geoIpData = $this->addressHelper->getGeoIpData();
        } else {
            $geoIpData = [
                'city' => '',
                'country_id' => strtoupper($forceful),
                'postcode' => ''
            ];
        }

        $result = [
            'selectedCountry' => $this->getWebsiteCode()
        ];

        if (!isset($geoIpData['country_id'])) {
            return $result;
        }

        $countryId = strtolower($geoIpData['country_id']);
        if (in_array($countryId, $this->allowedCountryCodes())) {
            return $result;
        }

        $blockModel = null;
        $blockIdentifier = sprintf('geoip_notice_%s', strtolower($geoIpData['country_id']));

        try {
            $blockModel = $this->getBlockByIdentifier->execute($blockIdentifier, 0);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning(
                sprintf('OnTap_StoreSwitcher did not find a localised block, lookup was "%s"', $blockIdentifier)
            );
        }

        if (!isset($blockModel)) {
            $blockModel = $this->getBlockByIdentifier->execute(sprintf('geoip_notice'), 0);
        }

        try {
            /** @var \Magento\Cms\Block\Block $block */
            $block = $this->layout->createBlock(
                \Magento\Cms\Block\Block::class
            );
            $block->setBlockId($blockModel->getId());
        } catch (\Exception $e) {
            $this->logger->critical('Unable to render GeoIp notice', ['exception' => $e]);
            return $result;
        }

        $result['location'] = $geoIpData;
        $result['noticeHtml'] = $block->toHtml();

        return $result;
    }
}
