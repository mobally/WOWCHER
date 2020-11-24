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

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        $logTable = $setup->getTable('cordial_sync_log');
        $connection->dropTable($logTable);

        $syncTable = $setup->getTable('cordial_sync_touched');
        $connection->dropTable($syncTable);

        $syncTemplateTable = $setup->getTable('cordial_sync_template');
        $connection->dropTable($syncTemplateTable);

        $connection->dropColumn($setup->getTable('sales_order'), \Cordial\Sync\Model\Api\Config::ATTR_CODE);
        $connection->dropColumn($setup->getTable('sales_order_grid'), \Cordial\Sync\Model\Api\Config::ATTR_CODE);

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, \Cordial\Sync\Model\Api\Config::ATTR_CODE);
        $eavSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, \Cordial\Sync\Model\Api\Config::ATTR_CODE);

        $setup->endSetup();
    }
}
