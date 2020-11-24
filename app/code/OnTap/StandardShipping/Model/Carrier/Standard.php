<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\StandardShipping\Model\Carrier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\ProductRepository;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class Standard extends AbstractCarrier implements CarrierInterface
{
    const POSTAGE_PRICE = 'product_postage_price';

    /**
     * @var string
     */
    protected $_code = 'standard';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var Cart
     */
    protected Cart $cart;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param Cart $cart
     * @param ProductRepository $productRepository
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Cart $cart,
        ProductRepository $productRepository,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Calculate rates
     *
     * @param RateRequest $request
     * @return bool|Result|\Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collectRates(RateRequest $request)
    {
        $shippingPrice = (float)0.00;

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $quoteProducts = $this->cart->getQuote()->getItemsCollection();

        foreach ($quoteProducts as $product) {
            $item = $this->productRepository->getById($product->getProductId());
            $postageCost = $this->getPostageCost($item);
            $shippingPrice += (float)$product->getQty() * (float)$postageCost;
        }

        $result = $this->_rateResultFactory->create();

        if ($shippingPrice !== false) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }

        return $result;
    }

    /**
     * Get allowed methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Get product attribute value
     *
     * @param ProductInterface $product
     * @return float|null
     */
    public function getPostageCost(ProductInterface $product): ?float
    {
        if (!empty($product->getData(self::POSTAGE_PRICE))) {
            return (float) $product->getData(self::POSTAGE_PRICE);
        }
        return 0;
    }
}
