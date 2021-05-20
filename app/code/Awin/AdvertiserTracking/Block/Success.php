<?php

namespace Awin\AdvertiserTracking\Block;

class Success extends \Magento\Checkout\Block\Onepage\Success
{

    protected $orderItemsDetails;
    protected $_cookieHandler;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\Order $orderItemsDetails,
        \Awin\AdvertiserTracking\Cookie\CookieHandler $cookieHandler,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->orderItemsDetails = $orderItemsDetails;
        $this->_cookieHandler = $cookieHandler;
    }

    public function getOrderItemsDetails()
    {
        try{
            $IncrementId  = $this->_checkoutSession->getLastRealOrder()->getIncrementId();
            $order = $this->orderItemsDetails->loadByIncrementId($IncrementId);
            return $order;
        }
        catch(\Exception $e){

        }
    }

    public function getChannelParameterValue()
    {
        try{
            $cookieChannel = $this->_cookieHandler->get('source');
            if($cookieChannel && strlen($cookieChannel) > 0){
                return $cookieChannel;
            }
        }catch(\Exception $e)
        {
           
        }
        return 'aw';
    }
}