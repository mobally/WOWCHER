<?php


namespace Rvs\CategoryDisable\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Categorydisable extends Command
{
protected $path;
public function __construct(\Rvs\CategoryDisable\Model\CategoryDisableAction $path) {
	parent::__construct();
	$this->path = $path;
	 }


    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $name = $input->getArgument(self::NAME_ARGUMENT);
        $option = $input->getOption(self::NAME_OPTION);
        $this->path->execute();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("wowcher:catalog:categorydisable");
        $this->setDescription("");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
}


	
