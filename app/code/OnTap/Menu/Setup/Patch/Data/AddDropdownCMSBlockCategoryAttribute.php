<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Menu\Setup\Patch\Data;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddDropdownCMSBlockCategoryAttribute implements DataPatchInterface, PatchRevertableInterface
{
    const ATTRIBUTE_CODE = 'dropdown_cms_static_block';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Create attribute
     *
     * @return AddDropdownCMSBlockCategoryAttribute|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            CategoryAttributeInterface::ENTITY_TYPE_CODE,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'varchar',
                'label' => 'Drop-down CMS static block',
                'input' => 'text',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 25,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Content',
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Remove Attribute
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CategorySetup $eavSetup */
        $eavSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(
            CategoryAttributeInterface::ENTITY_TYPE_CODE,
            self::ATTRIBUTE_CODE
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get Aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
