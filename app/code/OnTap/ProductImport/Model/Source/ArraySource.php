<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ProductImport\Model\Source;

use Magento\ImportExport\Model\Import\AbstractSource;

class ArraySource extends AbstractSource
{
    /**
     * @var array
     */
    protected array $data;

    /**
     * ArraySource constructor.
     * @param array $colNames
     * @param array $data
     */
    public function __construct(
        array $colNames,
        array $data
    ) {
        parent::__construct($colNames);
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function _getNextRow()
    {
        if (!isset($this->data[$this->_key])) {
            return false;
        }
        return $this->data[$this->_key];
    }
}
