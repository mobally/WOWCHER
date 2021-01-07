<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\GlobalSiteTag\Model\Session;

/**
 * Class BackendSession
 * @package Scommerce\GlobalSiteTag\Model\Session
 */
class BackendSession extends \Magento\Framework\Session\SessionManager
{
    const KEY_REFUND = 'refund';

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;

        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
    }

    /**
     * TODO Use data object instead array. Array just for decrease time to develop
     *
     * @param bool $clear
     * @return array
     * $data = [
     *  'transaction_id' => 'string',
     *  'value' => number,
     *  'currency' => 'USD',
     *  'tax' => currency,
     *  'shipping' => currency,
     *  'items' => [$item, $item, ...]
     * ]
     * $item = [
     *  'id' => 'string',
     *  'name' => 'string',
     *  'brand' => 'string',
     *  'category' => 'string',
     *  'variant' => 'string',
     *  'price' => currency,
     *  'quantity' => integer,
     *  'coupon' => 'string', // Not Used for refund case
     *  'list_position' => integer // Not Used for refund case
     * ]
     */
    public function getRefundData($clear = false)
    {
        return $this->getData(self::KEY_REFUND, $clear);
    }

    /**
     * @param array $data See above
     * @return $this
     * @see \Scommerce\GlobalSiteTag\Model\Session\BackendSession::getRefundData()
     */
    public function setRefundData($data = [])
    {
        return $this->storage->setData(self::KEY_REFUND, $data);
    }

    /**
     * @return $this
     */
    public function unsetRefundData()
    {
        return $this->storage->unsetData(self::KEY_REFUND);
    }
}
