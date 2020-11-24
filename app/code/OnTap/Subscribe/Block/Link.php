<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Subscribe\Block;

use Magento\Framework\View\Element\Html\Link as HtmlLink;

class Link extends HtmlLink
{
    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}
