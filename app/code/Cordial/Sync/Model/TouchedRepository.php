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

use Cordial\Sync\Api\TouchedRepositoryInterface;
use Cordial\Sync\Api\Data\TouchedSearchResultsInterfaceFactory;
use Cordial\Sync\Api\Data\TouchedInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Cordial\Sync\Model\ResourceModel\Touched as ResourceTouched;
use Cordial\Sync\Model\ResourceModel\Touched\CollectionFactory as TouchedCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class TouchedRepository implements touchedRepositoryInterface
{

    protected $resource;

    protected $touchedFactory;

    protected $touchedCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataTouchedFactory;

    private $storeManager;

    /**
     * @param ResourceTouched $resource
     * @param TouchedFactory $touchedFactory
     * @param TouchedInterfaceFactory $dataTouchedFactory
     * @param TouchedCollectionFactory $touchedCollectionFactory
     * @param TouchedSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceTouched $resource,
        TouchedFactory $touchedFactory,
        TouchedInterfaceFactory $dataTouchedFactory,
        TouchedCollectionFactory $touchedCollectionFactory,
        TouchedSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
    
        $this->resource = $resource;
        $this->touchedFactory = $touchedFactory;
        $this->touchedCollectionFactory = $touchedCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTouchedFactory = $dataTouchedFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Cordial\Sync\Api\Data\TouchedInterface $touched
    ) {

        try {
            $touched->getResource()->save($touched);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the touched: %1',
                $exception->getMessage()
            ));
        }
        return $touched;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($touchedId)
    {
        $touched = $this->touchedFactory->create();
        $touched->getResource()->load($touched, $touchedId);
        if (!$touched->getId()) {
            throw new NoSuchEntityException(__('Touched with id "%1" does not exist.', $touchedId));
        }
        return $touched;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
    
        $collection = $this->touchedCollectionFactory->create();
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
        \Cordial\Sync\Api\Data\TouchedInterface $touched
    ) {
    
        try {
            $touched->getResource()->delete($touched);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Touched: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($touchedId)
    {
        return $this->delete($this->getById($touchedId));
    }
}
