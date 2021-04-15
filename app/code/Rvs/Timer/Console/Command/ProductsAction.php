<?php
namespace Rvs\Timer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class ProductsAction extends Command
{
protected $path;

	public function __construct(
	\Rvs\Timer\Model\ProductProcessBatch $path,
	string $name = null
	) {
	parent::__construct($name);
	    $this->path = $path;
	 }
   protected function configure()
   {
       $this->setName('wowcher:catalog:timer-update-products');
       $this->setDescription('Update Product timer value');
       
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
       $this->path->execute();
   }
}
