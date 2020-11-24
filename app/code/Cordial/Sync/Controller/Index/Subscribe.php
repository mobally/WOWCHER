<?php
/**
 * Cordial/Magento Integration RFP
 *
 * @category    Cordial
 * @package     Cordial_Sync
 * @author      Cordial Team <info@cordial.com>
 * @copyright   Cordial (http://cordial.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cordial\Sync\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class Subscribe extends Action
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Cordial\Sync\Model\Api\Customer
     */
    protected $api;

    /**
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param ApiHelper $helper
     * @param Feed $feed
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        \Cordial\Sync\Model\Api\Customer $api
    ) {
    
        $this->logger = $logger;
        $this->api = $api;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {

        $email = $this->_request->getParam('backinstock_email');
        $productId = $this->_request->getParam('backinstock_product_id');
        try {
            $storeId = $this->getStoreId();
            $api = $this->api->load($storeId);
            $created = $api->widgetAlert($email, $productId, $storeId);
            if ($created) {
                $response = ['status' => 'success', 'message' => __('You have been successfully subscribed')];
            } else {
                $response = ['status' => 'false', 'message'=> __('Oops! Please try again later.')];
            }

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => __('Oops!')
            ];
        }

        $resultJson->setData($response);

        return $resultJson;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        /* @var $storeManager \Magento\Store\Model\StoreManagerInterface */
        $storeManager = $this->_objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
        return $storeManager->getStore()->getId();
    }
}
