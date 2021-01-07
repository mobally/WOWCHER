<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scommerce\GlobalSiteTag\Model\Config\Source;

class AccountType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'analytics', 'label' => __('Google Analytics')],
            ['value' => 'adwords', 'label' => __('Google AdWords')],
            ['value' => 'other', 'label' => __('Other')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'analytics' => __('Google Analytics'),
            'adwords' => __('Google AdWords'),
            'other' => __('Other')
        ];
    }
}
