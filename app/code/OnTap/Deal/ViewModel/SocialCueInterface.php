<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Deal\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;

interface SocialCueInterface extends \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function canShow(ProductInterface $product): bool;
}
