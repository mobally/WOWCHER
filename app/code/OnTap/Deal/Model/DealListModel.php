<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Deal\Model;

use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;

class DealListModel
{
    /**
     * @var array
     */
    protected array $items = [];

    /**
     * @var ?AbstractCollection
     */
    protected ?AbstractCollection $collection = null;

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function setCollection(AbstractCollection $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection(): ?AbstractCollection
    {
        return $this->collection;
    }
}
