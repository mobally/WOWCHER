<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Deal\ViewModel\SocialCue;

use Magento\Catalog\Api\Data\ProductInterface;
use OnTap\Deal\ViewModel\SocialCueInterface;

class BusinessImage implements SocialCueInterface
{
    const DISPLAY_BUSINESS_IMAGE = 'display_business';
    const BUSINESS_IMG_ALT = 'business_image_alt';
    const BUSINESS_IMG_URL = 'business_image_url';
    const WEB_ADDRESS = 'web_address';

    /**
     * Logic to show
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function canShow(ProductInterface $product): bool
    {
        $image = $this->getImgValue($product);
        return !empty($image) && $product->getData(self::DISPLAY_BUSINESS_IMAGE) === "1";
    }

    /**
     * Get image url
     *
     * @param ProductInterface $product
     * @return string|null
     */
    public function getImgValue(ProductInterface $product): ?string
    {
        if (!empty($product->getData(self::BUSINESS_IMG_URL))) {
            return (string) $product->getData(self::BUSINESS_IMG_URL);
        }
        return null;
    }

    /**
     * Get alt value
     *
     * @param ProductInterface $product
     * @return string|null
     */
    public function getAltValue(ProductInterface $product): ?string
    {
        if (!empty($product->getData(self::BUSINESS_IMG_ALT))) {
            return (string) $product->getData(self::BUSINESS_IMG_ALT);
        }
        return null;
    }

    /**
     * Get website url
     *
     * @param ProductInterface $product
     * @return string|null
     */
    public function getWebAddressValue(ProductInterface $product): ?string
    {
        if (!empty($product->getData(self::WEB_ADDRESS))) {
            return (string) $product->getData(self::WEB_ADDRESS);
        }
        return null;
    }
}
