<?php
namespace Rvs\CheckoutAttributes\Plugin\Checkout\Model;

class DefaultConfigProvider
{
    protected $_product;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $product
    )
    {
        $this->_product = $product;
    }

    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $items = $result['totalsData']['items'];



        /* for($i = 0; $i < count($items); $i++) {
            $productId = $result['quoteItemData'][$i]['product']['entity_id'];
            $product = $this->_product->getById($productId);
            $leadtime = $product->getResource()->getAttribute('lead_time')->getFrontend()->getValue($product);

            $result['quoteItemData'][$i]['sku'] = $productId;
              }*/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         for($i=0;$i<count($items);$i++){
			$productId = $result['quoteItemData'][$i]['product']['entity_id'];
			$product = $this->_product->getById($productId);
			if($product->getTypeId() == "configurable")
				{
					$quoteId = $items[$i]['item_id'];
					$quoteNext = ($quoteId + 1);
					$quote = $objectManager->create('\Magento\Quote\Model\Quote\Item')->load($quoteNext);
					$simpleProid = $quote->getProductId();
					$conproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($simpleProid);
					$leadtime = $conproduct->getResource()->getAttribute('lead_time')->getFrontend()->getValue($conproduct);
					$duedate = $conproduct->getResource()->getAttribute('due_date')->getFrontend()->getValue($conproduct);
					//$simpleProName = $quote->getName();
					//$items[$i]['childname'] = $simpleProName;
					$result['quoteItemData'][$i]['sku'] = $leadtime;
					$result['quoteItemData'][$i]['due'] = $duedate;
				}
			elseif($product->getTypeId() == "simple")
				{
					$productId = $result['quoteItemData'][$i]['product']['entity_id'];
					$product = $this->_product->getById($productId);
					$leadtime = $product->getResource()->getAttribute('lead_time')->getFrontend()->getValue($product);
					$duedate = $product->getResource()->getAttribute('due_date')->getFrontend()->getValue($product);
					

					$result['quoteItemData'][$i]['sku'] = $leadtime;
					$result['quoteItemData'][$i]['due'] = $duedate;
				}			

        }

        //var_dump($result);exit;

        return $result;
    }


}
