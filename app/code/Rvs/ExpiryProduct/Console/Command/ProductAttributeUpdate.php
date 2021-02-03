<?php
namespace Rvs\ExpiryProduct\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class ProductAttributeUpdate extends Command
{
protected $path;

	public function __construct(
	\Rvs\ExpiryProduct\Model\UpdateAttrProducts $path,
	string $name = null
	) {
	parent::__construct($name);
	    $this->path = $path;
	 }
   protected function configure()
   {
       $this->setName('wowcher:catalog:update-attribute-products');
       $this->setDescription('Update product attribute');
       
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
   
   $this->path->execute();
   }
}

