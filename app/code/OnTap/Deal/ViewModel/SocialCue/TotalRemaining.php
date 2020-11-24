<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Deal\ViewModel\SocialCue;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use OnTap\Deal\ViewModel\SocialCueInterface;

class TotalRemaining implements SocialCueInterface
{
    const TOTAL_REMAINING = 'total_remaining';
    const TOTAL_BOUGHT = 'total_bought';

    /**
     * @var Grouped
     */
    protected Grouped $grouped;

    /**
     * @var StockRegistryInterface
     */
    protected StockRegistryInterface $stockRegistry;

    /**
     * TotalRemaining constructor.
     * @param Grouped $grouped
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        Grouped $grouped,
        StockRegistryInterface $stockRegistry
    ) {
        $this->grouped = $grouped;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @inheritDoc
     */
    public function canShow(ProductInterface $product): bool
    {
        return true;
    }

    /**
     * @param ProductInterface $product
     * @return string|null
     */
    public function getValue(ProductInterface $product): ?string
    {
        if (!empty($product->getData(self::TOTAL_BOUGHT))) {
            return (string) $product->getData(self::TOTAL_BOUGHT);
        }
        return null;
    }

    /**
     * @param ProductModel $product
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMessage(ProductModel $product): ?string
    {
        $qty = 0;
        $collection = $this->grouped->getAssociatedProducts($product);

        foreach ($collection as $item) {
            $qty += $this->getStockItem($item->getId());
        }

        if ($qty > 0 && $qty < 10) {
            return __("ALMOST GONE - only %1 remaining!", $qty);
        } else if ($qty < 50) {
            return __('Limited Availability!');
        } else if ($this->getValue($product) > 100 && $qty > 0) {
            return __('IN HIGH DEMAND!');
        } else if ($this->getValue($product) > 25 && $qty > 0) {
            return __('Selling fast!');
        }
        return null;
    }

    /**
     * @param $productId
     * @return float
     */
    public function getStockItem($productId)
    {
        return $this->stockRegistry->getStockItem($productId)->getQty();
    }
}
