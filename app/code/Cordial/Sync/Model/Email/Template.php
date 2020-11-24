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

namespace Cordial\Sync\Model\Email;

class Template extends \Magento\Email\Model\Template
{
    protected function addEmailVariables($variables, $storeId)
    {
        $res = parent::addEmailVariables($variables, $storeId);
        try {
            $cordialVars = [];
            if ($this->_registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS)) {
                $cordialVars = $this->_registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS);
                $this->_registry->unregister(\Cordial\Sync\Helper\Config::CORDIAL_VARS);
            }
            $cordialVars['storeId'] = $storeId;
            $cordialVars['designParams'] = $this->getDesignParams();
            $cordialVars = array_merge($cordialVars, $res);
            $this->_registry->register(\Cordial\Sync\Helper\Config::CORDIAL_VARS, $cordialVars);
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
        return $res;
    }
}
