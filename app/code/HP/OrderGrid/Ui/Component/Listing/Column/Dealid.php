<?php

namespace HP\Ordergrid\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;


class Dealid extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;
    private $productRepository;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->productRepository = $productRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    public function loadMyProduct($sku)
	{
    return $this->productRepository->get($sku);
	}

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $productArr = [];
                $order  = $this->_orderRepository->get($items["entity_id"]);

                foreach ($order->getAllVisibleItems() as $item) {
                    $sku = $item->getSku();
                    $item_data = $this->loadMyProduct($sku);
                    $pro_id = $item->getId();
                    $parent_id = "";
                    $deal_id = $item_data->getDealId();
                    $parent_item_data = $this->loadMyProduct($deal_id);
                    $parent_id = $parent_item_data->getId();
			if($parent_id){
			$productArr[] = "<a href='https://www.wowcher.com/admin/catalog/product/edit/id/$parent_id' target='blank'>$deal_id</a>";
			}
                }
                $items['products_deal_id'] = implode(' - ', $productArr);
                unset($productArr);
            }
        }
        return $dataSource;
    }
}
