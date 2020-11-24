<?php
/**
 * Cordial/Magento Integration RFP
 *
 * @category    Cordial
 * @package     Cordial_Sync
 * @author      Cordial Team <info@cordial.com>
 * @copyright   Cordial (http://cordial.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cordial\Sync\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema adds new table `eav_attribute_option_swatch`
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $contextInterface)
    {
        $installer = $setup;
        $installer->startSetup();

        $logTableName = $installer->getTable('cordial_sync_log');
        $logTable = $installer->getConnection()
            ->newTable($logTableName)
            ->addColumn(
                'log_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Log ID'
            )
            ->addColumn(
                'method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'unsigned' => true, 'nullable' => false],
                'Method'
            )
            ->addColumn(
                'path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'unsigned' => true, 'nullable' => false],
                'Path'
            )
            ->addColumn(
                'options',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['identity' => false, 'unsigned' => false, 'nullable' => true],
                'Options'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'unsigned' => true, 'nullable' => false],
                'API Code'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'unsigned' => true, 'nullable' => false],
                'API Message'
            )
            ->addColumn(
                'body',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['identity' => false, 'unsigned' => false, 'nullable' => true],
                'API Response'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Date of Template Creation'
            )
            ->setComment('Cordial Log table');

        $installer->getConnection()->createTable($logTable);

        $syncTableName = $installer->getTable('cordial_sync_touched');
        $syncTable = $installer->getConnection()
            ->newTable($syncTableName)
            ->addColumn(
                'touched_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Sync ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Entity ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )
            ->addColumn(
                'entity_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Entity Type ID'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Status'
            )
            ->addColumn(
                'todo',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'To Do'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addColumn(
                'external_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'External ID'
            )
            ->addIndex(
                $installer->getIdxName(
                    $syncTableName,
                    ['entity_id', 'store_id', 'entity_type_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'store_id', 'entity_type_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('Cordial Sync table');

        $installer->getConnection()->createTable($syncTable);


        $syncTemplateTableName = $installer->getTable('cordial_sync_template');
        $syncTemplateTable = $installer->getConnection()
            ->newTable($syncTemplateTableName)
            ->addColumn(
                'template_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Sync ID'
            )
            ->addColumn(
                'template_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Template Code'
            )
            ->addColumn(
                'orig_template_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Orig Template Code'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addIndex(
                $installer->getIdxName(
                    $syncTemplateTableName,
                    ['orig_template_code', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['orig_template_code', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('Cordial Sync table');

        $installer->getConnection()->createTable($syncTemplateTable);

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            \Cordial\Sync\Model\Api\Config::ATTR_CODE,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Cordial Sync',
                'nullable' => false,
                'default' => 1,
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            \Cordial\Sync\Model\Api\Config::ATTR_CODE,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Cordial Sync',
                'nullable' => false,
                'default' => 1,
            ]
        );

        $installer->endSetup();
    }
}
