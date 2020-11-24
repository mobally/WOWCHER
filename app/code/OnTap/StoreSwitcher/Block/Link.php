<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\StoreSwitcher\Block;

use Magento\Framework\Math\Random;
use Magento\Framework\View\Element\Html\Link as HtmlLink;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Link extends HtmlLink
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * Link constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        WebsiteRepositoryInterface $websiteRepository,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        parent::__construct(
            $context,
            $data,
            $secureRenderer,
            $random,
        );
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Get All store views
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllStores()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $key => $value) {
            if ($value['code'] !== 'uk') {
                $options[] = [
                    'label' => __('%1 (%2)', $this->getWebsite($value['website_id']), $value['name']),
                    'value' => $value['code']
                ];
            }
        }
        sort($options);
        return $options;
    }

    /**
     * Get website name
     *
     * @param int $id
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsite($id)
    {
        $website = $this->websiteRepository->getById($id);
        return $website->getName();
    }

    /**
     * Get store Url by code
     *
     * @param string $code
     * @return string
     */
    public function getStoreUrl($code)
    {
        return $this->scopeConfig->getValue('web/secure/base_url') . $code;
    }

    /**
     * Get current store code
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}
