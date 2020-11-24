<?php declare(strict_types=1);
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ConfigurableGroupedProduct\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Catalog\Api\Data\ProductInterface;
use OnTap\ContextProvider\Block\ProductAwareTemplate;

class Configurable extends ProductAwareTemplate
{
    const ATTRIBUTE_PRODUCT_TYPE = 'productdisplay_type';
    const ATTRIBUTE_PRODUCT_ROW = 'productdisplay_row';
    const ATTRIBUTE_PRODUCT_COLUMN = 'productdisplay_column';

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @var array
     */
    protected array $serialized = [];

    /**
     * @var array
     */
    protected array $associatedProducts;

    /**
     * @var array
     */
    protected array $options;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected \Magento\Framework\Pricing\Helper\Data $priceHelper;

    /**
     * Configurable constructor.
     * @param Template\Context $context
     * @param Serializer $serializer
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Serializer $serializer,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param string $field
     * @return array
     */
    protected function getDataFromSerialisedField(string $field): array
    {
        if (isset($this->serialized[$field])) {
            return $this->serialized[$field];
        }

        $attribute = $this->getProduct()
            ->getCustomAttribute($field);

        if (empty($attribute)) {
            return [];
        }
        $value = $attribute->getValue();
        if (empty($value)) {
            return [];
        }

        $this->serialized[$field] = $this->serializer->unserialize($value);
        return $this->serialized[$field];
    }

    /**
     * @return string
     */
    public function getRowLabel(): string
    {
        $row = $this->getDataFromSerialisedField(self::ATTRIBUTE_PRODUCT_ROW);
        if (isset($row['header'])) {
            return $row['header'];
        }
        return '';
    }

    /**
     * @return string
     */
    public function getColumnLabel(): string
    {
        $column = $this->getDataFromSerialisedField(self::ATTRIBUTE_PRODUCT_COLUMN);
        if (isset($column['header'])) {
            return $column['header'];
        }
        return '';
    }

    /**
     * @return int
     */
    public function hasTwoOptions(): int
    {
        $column = $this->getDataFromSerialisedField(self::ATTRIBUTE_PRODUCT_COLUMN);
        return count($column['items']) > 1 ? 1 : 0;
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        if (isset($this->options)) {
            return $this->options;
        }

        $hasTwoOption = $this->hasTwoOptions();

        $row = $this->getDataFromSerialisedField(self::ATTRIBUTE_PRODUCT_ROW);

        if ($hasTwoOption) {
            $column = $this->getDataFromSerialisedField(self::ATTRIBUTE_PRODUCT_COLUMN);
        }

        $rowsAndColumns = [];

        foreach ($row['items'] as $rowItem) {
            if (!$hasTwoOption) {
                $product = $this->findProductByRowAndColumn($rowItem['id']);

                // If there is only one option
                // the simply try find the product with rowId,
                // if it does not exist then consider it out of stock
                if (!isset($product)) {
                    $rowsAndColumns[$rowItem['id']] = [
                        'header' => __('%1 - Now SOLD OUT', $rowItem['header']),
                        'id' => 0
                    ];
                } else {
                    $rowsAndColumns[$rowItem['id']] = [
                        'header' => __(
                            '%1 - now %2',
                            $rowItem['header'],
                            $this->priceHelper->currency($product->getFinalPrice(), true, false),
                        ),
                        'id' => $product->getId()
                    ];
                }
            } else {
                $priceGroup = [];
                $rowsAndColumns[$rowItem['id']] = [];

                foreach ($column['items'] as $columnItem) {
                    $product = $this->findProductByRowAndColumn($rowItem['id'], $columnItem['id']);

                    // If product with rowId and columnId is not returned, it means it's disabled or out of stock
                    // in that case consider it SOLD OUT
                    if (!isset($product)) {
                        $rowsAndColumns[$rowItem['id']]['items'][] = [
                            'header' => __('%1 - Now SOLD OUT', $columnItem['header']),
                            'id' => 0
                        ];
                    } else {
                        $rowsAndColumns[$rowItem['id']]['items'][] = [
                            'header' => __(
                                '%1 - Now %2',
                                $columnItem['header'],
                                $this->priceHelper->currency($product->getFinalPrice(), true, false)),
                            'id' => $product->getId()
                        ];
                        $priceGroup[] = $product->getFinalPrice();
                    }
                }

                // There are selectable options for second option or the whole row is sold out
                if (count($priceGroup) > 0) {
                    $rowsAndColumns[$rowItem['id']]['id'] = $rowItem['id'];
                    $rowsAndColumns[$rowItem['id']]['header'] = __(
                        '%1 - from %2',
                        $rowItem['header'],
                        $this->priceHelper->currency(min($priceGroup), true, false)
                    );
                } else {
                    // The whole row is sold out
                    $rowsAndColumns[$rowItem['id']]['id'] = 0;
                    $rowsAndColumns[$rowItem['id']]['header'] = __(
                        '%1 - Now SOLD OUT',
                        $rowItem['header']
                    );
                }
            }
        }

        $this->options = $rowsAndColumns;
        return $rowsAndColumns;
    }

    /**
     * @return string
     */
    public function getRowOptions(): string
    {
        $rows = array_values($this->getOptions());
        return $this->serializer->serialize($rows);
    }

    /**
     * @return string
     */
    public function getColumnOptions(): string
    {
        $groupedProduct = $this->getOptions();
        return $this->serializer->serialize($groupedProduct);
    }

    /**
     * @param int $rowId
     * @param int|null $columnId
     * @return ProductInterface|null
     */
    protected function findProductByRowAndColumn(int $rowId, ?int $columnId = null): ?ProductInterface
    {
        if (!isset($this->associatedProducts)) {
            $product = $this->getProduct();

            /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $type */
            $type = $childProducts = $product->getTypeInstance();

            $collection = $type->getAssociatedProductCollection($product)
                /* Stock status is automatically added */
                /* @see \Magento\Catalog\Model\ResourceModel\Product\Collection\AddStockStatusToCollection */
                ->addAttributeToSelect(
                    ['name', 'type_id', 'rowid', 'columnid', 'price', 'special_price']
                )
                ->addAttributeToFilter(
                    'status',
                    ['in' => $type->getStatusFilters($product)]
                );

            $this->associatedProducts = $collection->getItems();
        }

        foreach ($this->associatedProducts as $product) {
            if ($columnId) {
                if ((int) $product->getData('rowid') === $rowId && (int) $product['columnid'] === $columnId) {
                    return $product;
                }
            } else {
                if ((int) $product->getData('rowid') === $rowId) {
                    return $product;
                }
            }
        }
        return null;
    }

    /**
     * @param string $attributeCode
     * @return string|array|null
     */
    public function getProductAttributeText(string $attributeCode)
    {
        try {
            return $this->getProduct()->getAttributeText($attributeCode);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isProductConfigurable(): bool
    {
        return $this->getProductAttributeText(self::ATTRIBUTE_PRODUCT_TYPE) === 'dropdown';
    }
}
