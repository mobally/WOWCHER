<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ProductImport\Console\Command;

use OnTap\ProductImport\Model\Importer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OnTap\ProductImport\Model\FeedDownloader;
use Symfony\Component\Console\Input\InputOption;

class UpdateDeals extends Command
{
    /**
     * @var Importer
     */
    protected Importer $importer;

    /**
     * @var FeedDownloader
     */
    protected FeedDownloader $feedDownloader;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;
	const NAME = 'feed';
    /**
     * ImportDealFromUrl constructor.
     * @param Importer $importer
     * @param FeedDownloader $feedDownloader
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        Importer $importer,
        FeedDownloader $feedDownloader,
        LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
        $this->importer = $importer;
        $this->feedDownloader = $feedDownloader;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('wowcher:catalog:update-deals-from-feed')->setDescription('Update products from feed.');
        /*$this->addOption(
                self::NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Name'
            );*/
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importer->setLogger($this->logger);
        $this->importer->updateAll($input);
        return 0;
    }
}
