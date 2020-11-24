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

namespace Cordial\Sync\Api\Data;

interface TemplateInterface
{

    const TEMPLATE_ID = 'template_id';

    /**
     * Get template_id
     * @return string|null
     */
    public function getTemplateId();

    /**
     * Set template_id
     * @param string $template_id
     * @return \Cordial\Sync\Api\Data\TemplateInterface
     */
    public function setTemplateId($templateId, $template_id);
}
