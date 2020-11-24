<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Menu\Model;

class CategoryListModel
{
    /**
     * @var array
     */
    protected array $items = [];

    /**
     * Set items
     *
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
