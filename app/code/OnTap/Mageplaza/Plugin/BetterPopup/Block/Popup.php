<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\Mageplaza\Plugin\BetterPopup\Block;

class Popup
{
    /**
     * @param \Mageplaza\BetterPopup\Block\Popup $subject
     * @param string $htmlConfig
     * @return string
     */
    public function afterGetPopupContent(\Mageplaza\BetterPopup\Block\Popup $subject, string $htmlConfig): string
    {
        $search  = [
            '{{login_url}}',
            '{{privpolicy_url}}',
            '{{asset_url}}',
        ];

        $replace = [
            $subject->getUrl('customer/account/login'),
            $subject->getUrl('customer/account/login'),
            $subject->getViewFileUrl(''),
        ];

        return str_replace($search, $replace, $htmlConfig);
    }
}
