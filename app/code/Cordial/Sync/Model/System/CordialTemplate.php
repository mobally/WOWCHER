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

namespace Cordial\Sync\Model\System;

use Magento\Framework\Data\OptionSourceInterface;

class CordialTemplate extends \Magento\Framework\DataObject implements OptionSourceInterface
{

    /*
     * @var \Cordial\Sync\Model\Api\Email
     */
    protected $api = null;

    /**
     * Init model
     * Load Website, Group and Store collections
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Cordial\Sync\Model\Api\Email $api)
    {
        $this->api = $api;
    }

    public function getTemplates($storeId)
    {
        return $this->_getDefaultTemplatesAsOptionsArray($storeId);
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->_getDefaultTemplatesAsOptionsArray();
    }

    /**
     * Get default templates as options array
     *
     * @return array
     */
    protected function _getDefaultTemplatesAsOptionsArray($storeId = null)
    {
        $options = [];

        try {
            $api = $this->api->load($storeId);
            $templates = $api->getAllTemplates($storeId);

            foreach ($templates as $template) {
                if (isset($template['key']) && isset($template['name'])) {
                    $options[] = ['value' => $template['key'], 'label' => $template['name']];
                }
            }

            uasort(
                $options,
                function (array $firstElement, array $secondElement) {
                    return strcmp($firstElement['label'], $secondElement['label']);
                }
            );
        } catch (\Exception $e) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $messageManager = $objectManager->create('\Magento\Framework\Message\ManagerInterface');
            $messageManager->addExceptionMessage($e, __('Something went wrong while getting Cordial templates.'));
        }

        return $options;
    }
}
