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

namespace Cordial\Sync\Model\Email\Template;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function setTemplateIdentifier($templateIdentifier)
    {
        try {
            $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
            $cordialVars = [];
            if ($registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS)) {
                $cordialVars = $registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS);
                $registry->unregister(\Cordial\Sync\Helper\Config::CORDIAL_VARS);
            }

            $cordialVars['templateIdentifier'] = $templateIdentifier;
            $registry->register(\Cordial\Sync\Helper\Config::CORDIAL_VARS, $cordialVars);
        } catch (\Exception $e) {
            $logger = $this->objectManager->create(\Psr\Log\LoggerInterface::class);
            $logger->info($e->getMessage());
        }

        return parent::setTemplateIdentifier($templateIdentifier);
    }
}
