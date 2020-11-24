<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Deal\ViewModel\SocialCue;

use Magento\Catalog\Api\Data\ProductInterface;
use OnTap\Deal\ViewModel\SocialCueInterface;

class TotalBought implements SocialCueInterface
{
    const DISPLAY_CUE = 'display_bought';
    const TOTAL_BOUGHT = 'total_bought';

    /**
     * @inheritDoc
     */
    public function canShow(ProductInterface $product): bool
    {
        $totalBought = $this->getValue($product);
        return is_numeric($totalBought) && (int)$totalBought >= 0 && $product->getData(self::DISPLAY_CUE) === "1";
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
}
