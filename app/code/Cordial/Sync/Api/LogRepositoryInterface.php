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

namespace Cordial\Sync\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LogRepositoryInterface
{

    /**
     * Save Log
     * @param \Cordial\Sync\Api\Data\LogInterface $log
     * @return \Cordial\Sync\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Cordial\Sync\Api\Data\LogInterface $log);

    /**
     * Retrieve Log
     * @param string $logId
     * @return \Cordial\Sync\Api\Data\LogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($logId);

    /**
     * Retrieve Log matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Cordial\Sync\Api\Data\LogSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Log
     * @param \Cordial\Sync\Api\Data\LogInterface $log
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Cordial\Sync\Api\Data\LogInterface $log);

    /**
     * Delete Log by ID
     * @param string $logId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($logId);
}
