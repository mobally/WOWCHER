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

interface TemplateRepositoryInterface
{

    /**
     * Save Template
     * @param \Cordial\Sync\Api\Data\TemplateInterface $template
     * @return \Cordial\Sync\Api\Data\TemplateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Cordial\Sync\Api\Data\TemplateInterface $template
    );

    /**
     * Retrieve Template
     * @param string $templateId
     * @return \Cordial\Sync\Api\Data\TemplateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($templateId);

    /**
     * Retrieve Template matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Cordial\Sync\Api\Data\TemplateSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Template
     * @param \Cordial\Sync\Api\Data\TemplateInterface $template
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Cordial\Sync\Api\Data\TemplateInterface $template
    );

    /**
     * Delete Template by ID
     * @param string $templateId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($templateId);
}
