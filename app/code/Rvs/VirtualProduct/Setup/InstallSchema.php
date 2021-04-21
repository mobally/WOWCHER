<?php
namespace Rvs\VirtualProduct\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('rvs_voucher_list')
        )->addColumn(
            'voucher_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Voucher Id'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            255,
            [],
            'sku'
        )->addColumn(
            'child_sku',
            Table::TYPE_TEXT,
            255,
            [],
            'Child Sku'
        )->addColumn(
            'final_sku',
            Table::TYPE_TEXT,
            255,
            [],
            'Final Sku'
        )->addColumn(
            'voucher_code',
            Table::TYPE_TEXT,
            255,
            [],
            'Voucher Code'
        )->addColumn(
            'url',
            Table::TYPE_TEXT,
            255,
            [],
            'Virtual Product Url'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Voucher Sent?'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => NULL],
            'Order ID'
        )->addColumn(
            'expiration_date',
            Table::TYPE_TEXT,
            255,
            [],
            'Voucher Expiration Date'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'voucher_sent_at',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Voucher Sent Date'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
