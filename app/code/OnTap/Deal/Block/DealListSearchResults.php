<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\Deal\Block;

class DealListSearchResults extends DealList
{
    /**
     * {@inheritDoc}
     */
    public function getCategoryId()
    {
        return null;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getLayout()->getBlock('product_list_toolbar_pager');
        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /* @var $pagerBlock \Magento\Theme\Block\Html\Pager */
            $pagerBlock->setAvailableLimit([12]);

            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setFrameLength(
                null
            )->setJump(
                null
            )->setLimit(
                12
            )->setCollection(
                $this->getLoadedProductCollection()
            );
            return $pagerBlock->toHtml();
        }
        return '';
    }
}
