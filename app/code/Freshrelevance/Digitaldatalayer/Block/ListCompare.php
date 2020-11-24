<?php

namespace Freshrelevance\Digitaldatalayer\Block;

use Exception;
use Freshrelevance\Digitaldatalayer\Helper\Category as CategoryHelper;
use Freshrelevance\Digitaldatalayer\Helper\Config;
use Freshrelevance\Digitaldatalayer\Helper\Data;
use Freshrelevance\Digitaldatalayer\Helper\PageType;
use Freshrelevance\Digitaldatalayer\Helper\Product;
use Magento\Catalog\Block\Product\Context;

/**
 * Class ListCompare
 * @package Freshrelevance\Digitaldatalayer\Block
 */
class ListCompare extends AbstractBlock
{
    /**
     * @var \Magento\Catalog\Block\Product\Compare\ListCompare
     */
    protected $compareBlock;

    public function __construct(
        Data $dataHelper,
        Product $productHelper,
        CategoryHelper $catHelper,
        PageType $pageTypeHelper,
        Config $configHelper,
        \Magento\Catalog\Block\Product\Compare\ListCompare $compareBlock,
        Context $context,
        array $data = []
    ) {
        parent::__construct(
            $dataHelper,
            $productHelper,
            $catHelper,
            $pageTypeHelper,
            $configHelper,
            $context,
            $data
        );
        $this->compareBlock = $compareBlock;
        $this->registry     = $context->getRegistry();
    }

    /**
     * Get Ddl data for the current comparison of products
     * @return string
     */
    public function getDDLData()
    {
        try {
            $ddlData = $this->dataHelper->getBaseData();

            if ($this->configHelper->checkIsPageAvailableForDisposing($this->pageTypeHelper->getCurrentPage())) {
                $productCollection = $this->compareBlock->getItems();
                if ($productCollection->count() > 0) {
                    $ddlData['product'] = $this->enrichProductData($productCollection, false);
                }
            }

            return json_encode($ddlData, JSON_UNESCAPED_SLASHES);
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
            return "{}";
        }
    }
}
