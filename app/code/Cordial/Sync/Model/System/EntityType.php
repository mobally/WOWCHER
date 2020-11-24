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
use Cordial\Sync\Model\Api\ApiFactory;

class EntityType extends \Magento\Framework\DataObject implements OptionSourceInterface
{

    /**
     * @var \Magento\Eav\Model\Entity
     *
     */
    protected $entity;

    /**
     * Init model
     * Load Website, Group and Store collections
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Eav\Model\Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => $this->getEntityTypeId(ApiFactory::API_PRODUCT), 'label' => __('Product')],
            ['value' => $this->getEntityTypeId(ApiFactory::API_CUSTOMER), 'label' => __('Customer')],
            ['value' => $this->getEntityTypeId(ApiFactory::API_ORDER), 'label' => __('Order')]
        ];

        return $options;
    }

    /**
     * Get entity type id
     *
     * @return int
     */
    protected function getEntityTypeId($code)
    {
        return $this->entity->setType($code)->getTypeId();
    }
}
