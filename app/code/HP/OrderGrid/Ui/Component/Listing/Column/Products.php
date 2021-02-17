<?php

namespace HP\Ordergrid\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class Products extends Column
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
                    $merchant_api = $item_data->getMerchantApi();
                    if($merchant_api == 0){
                    $productArr[] = "<div style='text-align: center;'><img src='https://wowcher-staging.idevelopment.site/pub/media/close.svg' width='15px' /></div>";
                    }else{
                    $productArr[] = "<div style='text-align: center;'><img src='https://wowcher-staging.idevelopment.site/pub/media/tick.svg' width='15px' /></div>";
                    }
                    
                }
                $items['products'] = implode('  ', $productArr);
                unset($productArr);
            }
        }
        return $dataSource;
    }
}
