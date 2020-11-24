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


namespace Cordial\Sync\Controller\Adminhtml\Map;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Cordial\Sync\Helper\Data
     */
    protected $helperData;

    /*
     * @var \Cordial\Sync\Model\Api\Attribute
     */
    protected $api = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Cordial\Sync\Helper\Data $helperData
     * @param \Cordial\Sync\Model\Api\Attribute $api
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Cordial\Sync\Helper\Data $helperData,
        \Cordial\Sync\Model\Api\Attribute $api,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helperData = $helperData;
        $this->api = $api;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store');
        $notFoundCordial = [];
        $notFoundMagento = [];
        $result = ['status' => 'success'];
        try {
            $customerAttributesMap = $this->helperData->getCustomerAttributesMap();
            /* @var $api \Cordial\Sync\Model\Api\Attribute */
            $api = $this->api->load($storeId);
            $cordialAttributes = $api->getAllAttributes();
            $customerAttributes = $this->helperData->getCustomerAttributes($storeId);
            $customerEntityId = \Magento\Customer\Model\Customer::ENTITY;
            if (!empty($customerAttributesMap)) {
                $message = [];
                foreach ($customerAttributesMap as $attribute) {
                    $found = false;
                    $type = null;
                    if (!empty($cordialAttributes)) {
                        foreach ($cordialAttributes as $cordialAttribute) {
                            if ($attribute['cordial'] == $cordialAttribute['key']) {
                                $found = true;
                                $type = $cordialAttribute['type'];
                                break;
                            }
                        }
                    }
                    if (!$found) {
                        $notFoundCordial[$attribute['magento']] = $attribute['cordial'];
                    }

                    $found = false;
                    if (!empty($customerAttributes) && $customerAttributes['totalRecords']) {
                        foreach ($customerAttributes['items'] as $customerAttribute) {
                            if ($attribute['magento'] == $customerAttribute['attribute_code']) {
                                $found = true;
                                //create attributes on cordial
                                if (in_array($attribute['cordial'], $notFoundCordial)) {
                                    $success = false;
                                    try {
                                        $magentoAttribute = $this->helperData->getAttributeInfo($customerEntityId, $attribute['magento']);
                                        if ($magentoAttribute->getId()) {
                                            $res = $api->create($magentoAttribute, 'attribute', $attribute['cordial']);
                                            if ($res) {
                                                $success = true;
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        $this->logger->error($e->getMessage());
                                        $success = false;
                                    }
                                }
                                if (!in_array($attribute['cordial'], $notFoundCordial)) {
                                    $convertedType = $this->helperData->convertAttributeType($customerAttribute);
                                    if ($type != $convertedType) {
                                        $message[] = $attribute['cordial'] . __(' and ') . $attribute['magento'] . __(' attribute types are incompatible');
                                    }
                                }
                                if (in_array($attribute['cordial'], $notFoundCordial)) {
                                    if (isset($success) && $success) {
                                        if (($key = array_search($attribute['cordial'], $notFoundCordial)) !== false) {
                                            unset($notFoundCordial[$key]);
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }

                    if (!$found) {
                        $notFoundMagento[] = $attribute['magento'];
                        unset($notFoundCordial[$attribute['magento']]);
                    }
                }


                if (!empty($notFoundCordial)) {
                    $notFoundCordial = implode(',', $notFoundCordial);
                    $message[] = __('Can\'t create on Cordial: ') . $notFoundCordial;
                }
                if (!empty($notFoundMagento)) {
                    $notFoundMagento = implode(',', $notFoundMagento);
                    $message[] = __('Not found on Magento: ') . $notFoundMagento;
                }
                if (!empty($message)) {
                    $result = ['status' => 'error', 'message' => implode("\n", $message)];
                }
            }
            return $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = ['status' => 'error'];
            return $this->jsonResponse($result);
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        if ($this->_authorization->isAllowed('Cordial_Sync::sync_configuration') || $this->_authorization->isAllowed('Cordial_Sync::sync_config')) {
            return true;
        }

        return false;
    }
}
