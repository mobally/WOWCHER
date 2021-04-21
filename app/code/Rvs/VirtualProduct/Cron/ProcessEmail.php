<?php

namespace Rvs\VirtualProduct\Cron;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Exception\LocalizedException;
use Rvs\VirtualProduct\Model\Voucher;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Area;
use Magento\Contact\Model\ConfigInterface;
use Magento\Framework\DataObject;

class ProcessEmail
{
    const EMAIL_TEMPLATE = 'rvs_virtualproduct_voucher_email_template';

    protected $logger;
    protected $voucherCollection;
    protected $objectManager;

    protected $transportBuilder;
    protected $inlineTranslation;
    protected $orderRepository;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,        
        \Rvs\VirtualProduct\Model\ResourceModel\Voucher\CollectionFactory $voucherCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        ObjectManagerInterface $objectManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ConfigInterface $contactsConfig
    ) {
        $this->logger               = $logger;
        $this->voucherCollection    = $voucherCollectionFactory;
        $this->objectManager        = $objectManager;
        $this->transportBuilder     = $transportBuilder;
        $this->inlineTranslation    = $inlineTranslation;        
        $this->contactsConfig       = $contactsConfig;
        $this->orderRepository      = $orderRepository;
    }
    
    public function execute() {
        try {
                $voucherCollection = $this->voucherCollection->create()
                        ->addFieldToFilter('status',['eq'=>'1'])
                        ->addFieldToFilter('order_id',['notnull' => true]);

                if($voucherCollection->getSize()){
                    foreach ($voucherCollection as $voucher) {
                        if($voucher->getOrderId()){
                            $this->sendEmail($voucher->getId(), $voucher->getOrderId());
                        }
                    }
                }
        }catch (LocalizedException $e) {
            $message = 'Voucher Email Process Error: ';
            $this->logger->critical($message, ['exception' => $e]);            
        } catch (\Exception $e) {
            $message = 'Voucher Email Process Error: ';
            $this->logger->error($message, ['exception' => $e]);
        }
    }

    protected function sendEmail($voucherId, $orderId){        
        // load order by ID
        $this->inlineTranslation->suspend();

        try {

            $order = $this->orderRepository->get($orderId);
            $voucher = $this->objectManager->create(Voucher::class)->load($voucherId);

            $transport = [
                'order' => $order,
                'order_id' => $order->getId(),
                'store' => $order->getStore(),                
                'created_at_formatted' => $order->getCreatedAtFormatted(2),
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ],
                'voucher' => [
                    'code'  =>  $voucher->getVoucherCode(),
                    'url'   =>  $voucher->getUrl(),
                    'expiration_date'   =>  $voucher->getExpirationDate()
                ]
            ];

            $transportObject = new DataObject($transport);

            $customerEmail = $order->getCustomerEmail();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::EMAIL_TEMPLATE)
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $order->getStoreId()
                    ]
                )
                ->setTemplateVars($transportObject->getData())
                ->setFrom($this->contactsConfig->emailSender())
                ->addTo($customerEmail)
                ->setReplyTo($this->contactsConfig->emailRecipient(), 'Wowcher Team')
                ->getTransport();

            $transport->sendMessage();

            // Update voucher sent status
            $timezone =$this->objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $date = $timezone->date()->format('y-m-d H:i:s');

            $voucher->setData('status','2')
                ->setData('voucher_sent_at',$date);

            $voucher->save();
        } catch (LocalizedException $e) {
            $message = 'Voucher Email Error: ';
            $this->logger->critical($message, ['exception' => $e]);            
        } catch (\Exception $e) {
            $message = 'Voucher Email Error: ';
            $this->logger->error($message, ['exception' => $e]);
        } finally {
            $this->inlineTranslation->resume();            
        }
    }
}
