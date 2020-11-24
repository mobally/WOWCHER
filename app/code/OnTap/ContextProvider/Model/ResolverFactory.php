<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ContextProvider\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ResolverFactory implements ArgumentInterface
{
    /**
     * @var array
     */
    protected array $resolverPool;

    /**
     * ResolverFactory constructor.
     * @param array $resolverPool
     */
    public function __construct(
        array $resolverPool = []
    ) {
        $this->resolverPool = $resolverPool;
    }

    /**
     * @param string $layoutName
     * @return ResolverInterface
     * @throws RuntimeException
     */
    public function create(string $layoutName): ResolverInterface
    {
        if (isset($this->resolverPool[$layoutName])) {
            $class = $this->resolverPool[$layoutName];
            return ObjectManager::getInstance()->get($class);
        } else {
            throw new RuntimeException(__('%1 did not find a resolver for layout "%2"', self::class, $layoutName));
        }
    }
}
