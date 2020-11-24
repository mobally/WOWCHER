<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ContextProvider\Block;

use Magento\Framework\Exception\LocalizedException;

class ProductResolver extends Resolver
{
    /**
     * @var bool
     */
    protected bool $prepared = false;

    /**
     * {@inheritDoc}
     */
    protected function _prepareLayout()
    {
        $resolver = $this->getResolver();

        try {
            $resolver->applyToLayoutBlocks($this->getLayout());
            $this->prepared = true;
        } catch (LocalizedException $e) {
            $this->getLogger()->error($e->getMessage(), [
                'exception' => $e,
                'layout' => $this->getLayoutName(),
                'requestParams' => $this->getRequest()->getParams(),
            ]);
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        if ($this->prepared === false) {
            return '';
        }

        return parent::_toHtml();
    }
}
