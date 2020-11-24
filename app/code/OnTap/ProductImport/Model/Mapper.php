<?php declare(strict_types=1);
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ProductImport\Model;

use OnTap\ProductImport\Model\Mapper\Grouped;
use OnTap\ProductImport\Model\Mapper\Product;

class Mapper
{
    /**
     * @var Grouped
     */
    protected Grouped $groupedMapper;

    /**
     * @var Product
     */
    protected Product $simpleProductMapper;

    /**
     * Mapper constructor.
     * @param Grouped $groupedMapper
     * @param Product $simpleProductMapper
     */
    public function __construct(Grouped $groupedMapper, Product $simpleProductMapper)
    {
        $this->simpleProductMapper = $simpleProductMapper;
        $this->groupedMapper = $groupedMapper;
    }

    /**
     * @param array $from
     * @param array $to
     */
    public function map(array $from, array &$to)
    {
        $deal = $from;
        unset($deal['products']);

        // Children
        $_to = [];
        foreach ($from['products'] as $idx => $_productData) {
            $_productData['deal'] = $deal;
            $to[] = $this->simpleProductMapper->_map($_productData, $_to);
        }

        // Parent
        $_to = [];
        $to[] = $this->groupedMapper->_map($from, $_to);
    }
}
