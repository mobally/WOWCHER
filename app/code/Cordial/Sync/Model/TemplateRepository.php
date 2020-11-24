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

namespace Cordial\Sync\Model;

use Cordial\Sync\Api\TemplateRepositoryInterface;
use Cordial\Sync\Api\Data\TemplateSearchResultsInterfaceFactory;
use Cordial\Sync\Api\Data\TemplateInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Cordial\Sync\Model\ResourceModel\Template as ResourceTemplate;
use Cordial\Sync\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class TemplateRepository implements templateRepositoryInterface
{

    protected $resource;

    protected $templateFactory;

    protected $templateCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataTemplateFactory;

    private $storeManager;

    /**
     * @param ResourceTemplate $resource
     * @param TemplateFactory $templateFactory
     * @param TemplateInterfaceFactory $dataTemplateFactory
     * @param TemplateCollectionFactory $templateCollectionFactory
     * @param TemplateSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceTemplate $resource,
        TemplateFactory $templateFactory,
        TemplateInterfaceFactory $dataTemplateFactory,
        TemplateCollectionFactory $templateCollectionFactory,
        TemplateSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->templateFactory = $templateFactory;
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTemplateFactory = $dataTemplateFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Cordial\Sync\Api\Data\TemplateInterface $template
    ) {
        try {
            $template->getResource()->save($template);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the template: %1',
                $exception->getMessage()
            ));
        }
        return $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($templateId)
    {
        $template = $this->templateFactory->create();
        $template->getResource()->load($template, $templateId);
        if (!$template->getId()) {
            throw new NoSuchEntityException(__('Template with id "%1" does not exist.', $templateId));
        }
        return $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->templateCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Cordial\Sync\Api\Data\TemplateInterface $template
    ) {
        try {
            $template->getResource()->delete($template);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Template: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($templateId)
    {
        return $this->delete($this->getById($templateId));
    }
}
