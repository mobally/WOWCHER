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

interface TouchedSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Touched list.
     * @return \Cordial\Sync\Api\Data\TouchedInterface[]
     */
    public function getItems();

    /**
     * Set sync_id list.
     * @param \Cordial\Sync\Api\Data\TouchedInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
