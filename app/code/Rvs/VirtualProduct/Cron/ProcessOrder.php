<?php

namespace Rvs\VirtualProduct\Cron;

use Rvs\VirtualProduct\Model\Voucher;
use Magento\Framework\ObjectManagerInterface;

class ProcessOrder
{
    const SYNC_PERIOD = '- 10 minutes';

    protected $logger;
    
    protected $orderItemCollection;

    protected $voucherCollection;

    protected $objectManager;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Rvs\VirtualProduct\Model\ResourceModel\Voucher\CollectionFactory $voucherCollectionFactory,
        ObjectManagerInterface $objectManager
    ) {
        $this->logger = $logger;
        $this->orderItemCollection  = $orderItemCollectionFactory;
        $this->voucherCollection    = $voucherCollectionFactory;
        $this->objectManager = $objectManager;
    }
    
    public function execute() {
        try {
                $itemCollection = $this->orderItemCollection->create()
                    ->addFieldToSelect(['order_id','sku','created_at'])
                    ->addFieldToFilter('created_at', array('from' => date('Y-m-d h:i:s', strtotime(self::SYNC_PERIOD))))
                    //->setOrder('created_at','desc')
                    ;
                foreach ($itemCollection as $item) {

                    $voucherCollection = $this->voucherCollection->create()
                        ->addFieldToSelect('voucher_id')
                        ->addFieldToFilter('status',['eq'=>'0'])
                        ->addFieldToFilter('order_id',['null' => true])
                        //->addFieldToFilter('order_id',['neq'=>$item->getOrderId()])
                        ->addFieldToFilter('final_sku',['eq'=>$item->getSku()]);                    
                    
                    if($voucherCollection->getSize()){
                        $voucherId = $voucherCollection->getFirstItem()->getVoucherId();
                        if($voucherId){
                            $VoucherModel = $this->objectManager->create(Voucher::class)->load($voucherId);                            
                            $VoucherModel->setData('order_id',$item->getOrderId());
                            $VoucherModel->setData('status','1');
                            $VoucherModel->save();
                        }
                    }
                }
        } catch (\Exception $e) {
            //$message = sprintf('Unable to delete expired quote (ID: %s)', $quoteId);
            $message = 'Error: ';
            $this->logger->error($message, ['exception' => $e]);
        }
    }
}
