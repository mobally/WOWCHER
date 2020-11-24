<?php

namespace Freshrelevance\Digitaldatalayer\Block;

use Freshrelevance\Digitaldatalayer\Helper\Category as CategoryHelper;
use Freshrelevance\Digitaldatalayer\Helper\Config;
use Freshrelevance\Digitaldatalayer\Helper\Data;
use Freshrelevance\Digitaldatalayer\Helper\PageType;
use Freshrelevance\Digitaldatalayer\Helper\Product as ProductHelper;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\AbstractCollection;

/**
 * Class AbstractBlock
 * @package Freshrelevance\Digitaldatalayer\Block
 */
abstract class AbstractBlock extends AbstractProduct
{
    /**
     * @var CategoryHelper
     */
    protected $catHelper;
    /**
     * @var Data
     */
    protected $dataHelper;
    /**
     * @var Config
     */
    protected $configHelper;
    /**
     * @var PageType
     */
    protected $pageTypeHelper;
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    public function __construct(
        Data $dataHelper,
        ProductHelper $productHelper,
        CategoryHelper $catHelper,
        PageType $pageTypeHelper,
        Config $configHelper,
        Context $context,

        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper     = $dataHelper;
        $this->catHelper      = $catHelper;
        $this->pageTypeHelper = $pageTypeHelper;
        $this->configHelper   = $configHelper;
        $this->productHelper  = $productHelper;
        $this->registry       = $context->getRegistry();
    }

    /**
     * Helper to check if module is enabled.
     * @return bool
     */
    public function moduleEnabled()
    {
        return (bool)$this->configHelper->getEnabledDdl();
    }

    /**
     * The default product collection in the parent block does not contain all needed attributes for the call.
     * Here we're making a separate call to get all products with full data sets in one hit.
     * @param $products
     * @param bool $sortOrder
     * @return array
     */
    public function enrichProductData($products, $sortOrder = true)
    {
        $productIds = [];
        $result     = [];

        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $enrichedProducts = $this->productHelper->getProductsByIDS($productIds, $sortOrder);

        //Passing in the enriched product to the original get product data call
        foreach ($enrichedProducts as $product) {
            $result[] = $this->dataHelper->getProductData($product->getId(), null, $product);
        }

        return $result;
    }

    /**
     * Get ddl data for the specific block
     * @return string
     */
    abstract public function getDDLData();
}
