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

namespace Cordial\Sync\Model;

use Cordial\Sync\Api\Data\TemplateInterface;

class Template extends \Magento\Framework\Model\AbstractModel implements TemplateInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Cordial\Sync\Model\ResourceModel\Template::class);
    }

    /**
     * Get template_id
     * @return string
     */
    public function getTemplateId()
    {
        return $this->getData(self::TEMPLATE_ID);
    }

    /**
     * Set template_id
     * @param string $templateId
     * @return \Cordial\Sync\Api\Data\TemplateInterface
     */
    public function setTemplateId($templateId, $template_id)
    {
        return $this->setData(self::TEMPLATE_ID, $template_id);
    }

    /**
     * Load by orig template code.
     *
     * @param string $origTemplateCode
     * @param storeId
     * @return array
     */
    public function loadByOrigCode($origTemplateCode, $storeId = null)
    {
        return $this->getResource()->loadByOrigCode($origTemplateCode, $storeId);
    }
}
