<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\Menu\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddMenuItemsCMSBlock implements DataPatchInterface
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @param BlockFactory $blockFactory
     * @param BlockRepository $blockRepository
     */
    public function __construct(
        BlockFactory $blockFactory,
        BlockRepository $blockRepository
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * Create blocks
     *
     * @return AddMenuItemsCMSBlock|void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function apply()
    {
        $data = [
            'title' => 'Custom Menu Items',
            'identifier' => 'custom-menu-items',
            'stores' => ['0'],
            'is_active' => 1,
            'content' => ''
        ];
        $newBlock = $this->blockFactory->create(['data' => $data]);
        $this->blockRepository->save($newBlock);
    }

    /**
     * Get Alias
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
