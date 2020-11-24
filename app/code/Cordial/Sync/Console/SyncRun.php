<?php

namespace Cordial\Sync\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncRun extends Command {
	protected function configure() {
		   $this->setName('cordial:syncrun');
		   $this->setDescription('Run Sync process');
		   
		   parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
		$output->writeln("Sync in progress...");
	    $this->cronjob->execute();
	}

	public function __construct(
        \Magento\Framework\App\State $state,
        \Cordial\Sync\Cron\Cronjob $cronjob
    ) {
        $this->cronjob	= $cronjob;
        $this->state    = $state;
        parent::__construct();
    }
}
