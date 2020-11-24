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

namespace Cordial\Sync\Model\ResourceModel;

class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cordial_sync_template', 'template_id');
    }

    public function loadByOrigCode($origTemplateCode, $storeId = null)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'orig_template_code = :orig_template_code'
        )->where(
            'store_id = :store_id'
        );
        $bind = [
            'orig_template_code' => $origTemplateCode,
            'store_id' => $storeId
        ];

        return $this->getConnection()->fetchRow($select, $bind);
    }
}
