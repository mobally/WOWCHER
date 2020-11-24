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

interface TouchedInterface
{

    const TOUCHED_ID = 'touched_id';

    /**
     * Get touched_id
     * @return string|null
     */
    public function getTouchedId();

    /**
     * Set touched_id
     * @param string $touched_id
     * @return \Cordial\Sync\Api\Data\TouchedInterface
     */
    public function setTouchedId($touchedId);
}
