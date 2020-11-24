<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ContextProvider\Plugin\GroupedProduct\Block\Stockqty\Type;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Block\Stockqty\Type\Grouped;
use OnTap\ContextProvider\Model\ResolverFactory;

class GroupedPlugin
{
    /**
     * @var ResolverFactory
     */
    protected ResolverFactory $resolverFactory;

    /**
     * GroupedPlugin constructor.
     * @param ResolverFactory $resolverFactory
     */
    public function __construct(ResolverFactory $resolverFactory)
    {
        $this->resolverFactory = $resolverFactory;
    }

    /**
     * @param Grouped $subject
     * @param \Closure $proceed
     * @return array[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetIdentities(Grouped $subject, \Closure $proceed)
    {
        try {
            $context = $subject->getLayout()->getBlock('context-provider')->getContext();
            $resolver = $this->resolverFactory->create($context);
            $resolver->getProduct();
            return $proceed();
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }
}
