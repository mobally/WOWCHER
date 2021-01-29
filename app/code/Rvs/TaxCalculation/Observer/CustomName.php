<?php
namespace Rvs\TaxCalculation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class CustomName implements ObserverInterface
{

    protected $_productRepository;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Catalog\Model\ProductRepository $productRepository)
    {
        $this->_productRepository = $productRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()
            ->getData('quote_item');
        $item = ($item->getParentItem() ? $item->getParentItem() : $item);
        $productId = $item->getProduct()
            ->getId();
        $child_sku = $item->getProduct()
            ->getSku();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\GroupedProduct\Model\Product\Type\Grouped')
            ->getParentIdsByChild($productId);
        if (isset($product[0]))
        {
            $parent_id = $product[0];
            $_product = $this->getProductById($parent_id);
            $name = $child_sku . ' - ' . $_product->getName();
            $item->getProduct()
                ->setName($name);
            $item->setName($name);
            $item->getProduct()
                ->setIsSuperMode(true);
        }

    }
    public function getProductById($id)
    {
        return $this
            ->_productRepository
            ->getById($id);
    }
}


