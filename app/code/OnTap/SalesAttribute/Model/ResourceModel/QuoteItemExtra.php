<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\SalesAttribute\Model\ResourceModel;

class QuoteItemExtra extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const QUOTE_ITEM_EXTRA_TABLE = 'quote_item_extra';

    /**
     * @inheritdoc
     */
    protected $_useIsObjectNew = true;

    /**
     * @inheritdoc
     */
    protected $_isPkAutoIncrement = false;

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(self::QUOTE_ITEM_EXTRA_TABLE, 'quote_item_id');
    }
}
