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

interface TouchedRepositoryInterface
{

    /**
     * Save Touched
     * @param \Cordial\Sync\Api\Data\TouchedInterface $touched
     * @return \Cordial\Sync\Api\Data\TouchedInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Cordial\Sync\Api\Data\TouchedInterface $touched
    );

    /**
     * Retrieve Touched
     * @param string $touchedId
     * @return \Cordial\Sync\Api\Data\TouchedInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($touchedId);

    /**
     * Retrieve Touched matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Cordial\Sync\Api\Data\TouchedSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Touched
     * @param \Cordial\Sync\Api\Data\TouchedInterface $touched
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Cordial\Sync\Api\Data\TouchedInterface $touched
    );

    /**
     * Delete Touched by ID
     * @param string $touchedId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($touchedId);
}
