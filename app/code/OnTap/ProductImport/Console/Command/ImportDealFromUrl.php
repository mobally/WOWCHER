<?php declare(strict_types=1);
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ProductImport\Console\Command;

use OnTap\ProductImport\Model\Importer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportDealFromUrl extends Command
{
    const INPUT_KEY_ULR = 'url';

    /**
     * @var Importer
     */
    protected Importer $importer;

    /**
     * ImportDealFromUrl constructor.
     * @param Importer $importer
     * @param string|null $name
     */
    public function __construct(
        Importer $importer,
        string $name = null
    ) {
        parent::__construct($name);
        $this->importer = $importer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('wowcher:catalog:import-product-from-url')
            ->setDescription('Imports a product directly from deal url.')
            ->setDefinition($this->getInputList());
    }

    /**
     * Get list of options and arguments for the command
     * Example URL: https://public-api.wowcher.co.uk/v1/deal/15672451
     * @return array
     */
    public function getInputList(): array
    {
        return [
            new InputArgument(
                self::INPUT_KEY_ULR,
                InputArgument::REQUIRED,
                'A URL that contains a Wowcher deal in JSON format e.g. https://public-api.wowcher.co.uk/v1/deal/[x] where x is the deal ID'
            )
        ];
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument(self::INPUT_KEY_ULR);
        $this->importer->importFromUrl($url);
        return 0;
    }
}
