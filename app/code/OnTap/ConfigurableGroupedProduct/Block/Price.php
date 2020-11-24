<?php declare(strict_types=1);
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ConfigurableGroupedProduct\Block;

use OnTap\ContextProvider\Block\ProductAwareTemplate;

class Price extends ProductAwareTemplate
{
    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRenderPool()
    {
        return $this->getLayout()->createBlock(
            \Magento\Framework\Pricing\Render\RendererPool::class,
            'render.configurable-ui.prices',
            [
                'data' => [
                    'default' => [
                        'default_amount_render_class' => 'Magento\Framework\Pricing\Render\Amount',
                        'default_amount_render_template' => 'Magento_Catalog::product/price/amount/default.phtml',
                    ]
                ]
            ]
        );
    }
}
