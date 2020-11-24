<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use OnTap\ContextProvider\Model\ResolverFactory;
use OnTap\ContextProvider\Model\ResolverInterface;

abstract class Resolver extends Template
{
    /**
     * @return string
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function getLayoutName(): string
    {
        $contextSettings = $this->getLayout()->getBlock('context-provider');
        if (!($contextSettings instanceof AbstractBlock)) {
            throw new RuntimeException(__('ContextAwareView is being used without context, make sure you have "context-provider" block in the layout.'));
        }

        return $contextSettings->getContext();
    }

    /**
     * @return ResolverInterface
     * @throws RuntimeException
     * @throws LocalizedException
     */
    public function getResolver(): ResolverInterface
    {
        /** @var ResolverFactory $resolverFactory */
        $resolverFactory = $this->getResolverFactory();
        return $resolverFactory->create(
            $this->getLayoutName()
        );
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return $this->_logger;
    }
}
