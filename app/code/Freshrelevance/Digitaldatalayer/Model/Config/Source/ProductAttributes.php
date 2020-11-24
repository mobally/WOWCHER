<?php
namespace Freshrelevance\Digitaldatalayer\Model\Config\Source;

class ProductAttributes implements \Magento\Framework\Option\ArrayInterface
{
    private $attributeFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
    }
    public function toOptionArray()
    {
        $attributes = $this->attributeFactory->getCollection();
        $attributeArray = [['label' => 'none', 'value' => '0'], ['label' => 'all', 'value' => 'all']];
        foreach ($attributes as $a) {
            $attrCode = $a->getAttributeCode();
            if ($a->getData('is_user_defined')) {
                array_push($attributeArray, ['label' => $attrCode, 'value' => $attrCode]);
            }
        }

        return $attributeArray;
    }
}
