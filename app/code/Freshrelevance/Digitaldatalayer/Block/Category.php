<?php
namespace Freshrelevance\Digitaldatalayer\Block;
use Exception;
use InvalidArgumentException;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
/**
 * Class Category
 * @package Freshrelevance\Digitaldatalayer\Block
 */
class Category extends AbstractBlock
{
    /**
     * @var CategoryModel
     */
    protected $currentCategory;
    /**
     * @var bool|ListProduct
     */
    protected $listBlock;

    /**
     * @return string
     */
    public function getDDLData()
    {
        $currentCategory = $this->currentCategory ?: $this->registry->registry('current_category');
        $ddlData         = '{}';
        if ($currentCategory) {
            try {
                $catTree = $this->catHelper->getCategoryTree($currentCategory);
                $ddlData = $this->dataHelper->getBaseData($catTree);
                if ($this->configHelper->checkIsPageAvailableForDisposing($this->pageTypeHelper->getCurrentPage())) {
                    if ($products = $this->getLoadedProductCollection()) {
                        if ($products->count() > 0) {
                            $ddlData['product'] = $this->enrichProductData($products);
                        }
                    }
                }
                $ddlData = $this->dataHelper->jsonSerialize($ddlData);
            } catch (InvalidArgumentException $jsonSerializeException) {
                $this->_logger->error("Unable to serialize DDL Data: {$jsonSerializeException->getMessage()}:\n\n 
                {$jsonSerializeException->getTraceAsString()}");
            } catch (Exception $exception) {
                $this->_logger->error($exception->getMessage());
            }
        }
        return $ddlData;
    }
    /**
     * @return AbstractCollection|null
     * @throws LocalizedException
     */
    public function getLoadedProductCollection()
    {
        /** @var ListProduct $block */
        if ($this->listBlock = $this->getLayout()->getBlock('category.products.list')) {
            return $this->listBlock->getLoadedProductCollection();
        }
        return null;
    }
}