<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Deal\Plugin\Catalog\Helper;

class DataPlugin
{
    /**
     * @param \Magento\Catalog\Helper\Data $subject
     * @param array $path
     * @return array
     */
    public function afterGetBreadcrumbPath(\Magento\Catalog\Helper\Data $subject, array $path): array
    {
        if (isset($path['product'])) {
            unset($path['product']);
        }
        return $path;
    }
}
