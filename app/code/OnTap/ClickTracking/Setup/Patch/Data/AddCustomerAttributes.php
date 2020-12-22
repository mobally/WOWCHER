<?php
/**
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ClickTracking\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use OnTap\ClickTracking\Model\Tracking;

class AddCustomerAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * AddCustomerPhoneNumberAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            Tracking::GCLID,
            [
                'type' => 'varchar',
                'label' => 'GCLID',
                'input' => 'text',
                'required' => false,
                'default' => '',
                'visible' => true,
                'visible_on_front' => false,
                'system' => false,
                'user_defined' => true,
                'used_in_grid' => true,
                'visible_in_grid' => true,
                'filterable_in_grid' => true,
                'searchable_in_grid' => false,
                'position' => 900,
            ]
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            'gclid'
        );
        $attribute->addData(
            [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ]
        );
        $attribute->save();

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            Tracking::MSCLKID,
            [
                'type' => 'varchar',
                'label' => 'MSCLKID',
                'input' => 'text',
                'required' => false,
                'default' => '',
                'visible' => true,
                'visible_on_front' => false,
                'system' => false,
                'user_defined' => true,
                'used_in_grid' => true,
                'visible_in_grid' => true,
                'filterable_in_grid' => true,
                'searchable_in_grid' => false,
                'position' => 901,
            ]
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            'msclkid'
        );
        $attribute->addData(
            [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ]
        );
        $attribute->save();

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            Tracking::ITO,
            [
                'type' => 'varchar',
                'label' => 'Subscription source (ITO)',
                'input' => 'text',
                'required' => false,
                'default' => '',
                'visible' => true,
                'visible_on_front' => false,
                'system' => false,
                'user_defined' => true,
                'used_in_grid' => true,
                'visible_in_grid' => true,
                'filterable_in_grid' => true,
                'searchable_in_grid' => false,
                'position' => 902,
            ]
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            'ito'
        );
        $attribute->addData(
            [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer']
            ]
        );
        $attribute->save();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
