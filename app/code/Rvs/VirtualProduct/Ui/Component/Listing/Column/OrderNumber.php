<?php

namespace Rvs\VirtualProduct\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class OrderNumber extends Column
{
	protected $escaper; 
	protected $systemStore;
	protected $orderFactory; 
 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
		$this->escaper 		= $escaper;
		$this->orderFactory = $orderFactory;
		parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
        	$fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
            	if($item[$fieldName] != '') {
            		$order = $this->orderFactory->create()->load((int)$item[$fieldName]);
                	$item[$fieldName] = $order->getIncrementId();
            	}
            }
        }
 
        return $dataSource;
    }
}
