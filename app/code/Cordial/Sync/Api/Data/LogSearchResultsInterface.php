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

interface LogSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Log list.
     * @return \Cordial\Sync\Api\Data\LogInterface[]
     */
    public function getItems();

    /**
     * Set log_id list.
     * @param \Cordial\Sync\Api\Data\LogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
