<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\Deal\ViewModel\SocialCue;

use Magento\Catalog\Api\Data\ProductInterface;
use OnTap\Deal\ViewModel\SocialCueInterface;

class SaveUpTo implements SocialCueInterface
{
    /**
     * @var string[]
     */
    protected array $discounts;

    /**
     * @inheritDoc
     */
    public function canShow(ProductInterface $product): bool
    {
        return $product->getData('display_discount') === '1' && $this->getValue($product) !== null;
    }

    /**
     * @param ProductInterface $product
     * @return string|null
     */
    public function getValue(ProductInterface $product): ?string
    {
        if (isset($this->discounts[$product->getId()])) {
            return $this->discounts[$product->getId()];
        }

        $minProduct = $product
            ->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
            ->getMinProduct();

        if (!$minProduct) {
            return null;
        }

        if ($minProduct->getPrice() <= $minProduct->getSpecialPrice()) {
            return null;
        }

        $discount = (float) $minProduct->getSpecialPrice() * 100 / (float)$minProduct->getPrice();
        $this->discounts[$product->getId()] = sprintf('%s%%', 100 - ceil($discount));

        return $this->discounts[$product->getId()];
    }
}
