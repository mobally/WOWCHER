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

namespace Cordial\Sync\Model\Api;

interface SyncInterface
{

    /**
     * Create entity
     *
     * @param $entity
     * @return boolean
     */
    public function create($entity);

    /**
     * Update entity
     *
     * @param $entity
     * @return boolean
     */
    public function update($entity);
}
