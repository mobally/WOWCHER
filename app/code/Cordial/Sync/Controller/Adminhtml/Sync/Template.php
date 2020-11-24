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

namespace Cordial\Sync\Controller\Adminhtml\Sync;

class Template extends \Magento\Backend\App\Action
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
     * @var \Cordial\Sync\Model\System\CordialTemplate
     */
    protected $cordialTemplate;

    /**
     * @var \Cordial\Sync\Model\System\Template
     */
    protected $template;

    /**
     * @var \Cordial\Sync\Model\Api\Email
     */
    protected $api;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @var \Cordial\Sync\Model\Variable
     */
    protected $variable;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Cordial\Sync\Helper\Data
     */
    protected $helperData;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Cordial\Sync\Helper\Data $helperData
     * @param \Cordial\Sync\Model\System\CordialTemplate $cordialTemplate
     * @param \Cordial\Sync\Model\System\Template $template
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Cordial\Sync\Helper\Data $helperData,
        \Cordial\Sync\Model\System\CordialTemplate $cordialTemplate,
        \Cordial\Sync\Model\System\Template $template,
        \Cordial\Sync\Model\Api\Email $api,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Backend\Block\Template\Context $templateContext,
        \Cordial\Sync\Model\Variable $variable,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helperData = $helperData;
        $this->cordialTemplate = $cordialTemplate;
        $this->template = $template;
        $this->api = $api;
        $this->emailConfig = $emailConfig;
        $this->variable = $variable;
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

        try {
            $storeId = $this->getRequest()->getParam('store');
            $cordialTemplateOptions = $this->cordialTemplate->getTemplates($storeId);
            $templateOptions = $this->template->toOptionArray();
            $templateError = [];
            foreach ($templateOptions as $templateOption) {
                if (empty($templateOption['value'])) {
                    continue;
                }

                $templateCode = null;
                foreach ($cordialTemplateOptions as $cordialTemplateOption) {
                    if (empty($cordialTemplateOption['value'])) {
                        continue;
                    }

                    if (\Cordial\Sync\Model\Api\Config::API_EL_PREF . str_replace('/', '_', $templateOption['value']) === $cordialTemplateOption['value']) {
                        $templateCode = $cordialTemplateOption['value'];
                        break;
                    }
                }

//                Create template on cordial
                if (is_null($templateCode)) {
                    /**
                     * @var $templateBackend \Magento\Email\Model\BackendTemplate
                     */
                    $templateBackend = $this->_objectManager->create(\Magento\Email\Model\BackendTemplate::class);
                    $templateId = $templateOption['value'];
                    $parts = $this->emailConfig->parseTemplateIdParts($templateId);
                    $templateId = $parts['templateId'];
                    $theme = $parts['theme'];
                    if ($theme) {
                        $templateBackend->setForcedTheme($templateId, $theme);
                    }
                    $templateBackend->setForcedArea($templateId);
                    $templateBackend->loadDefault($templateId);
                    $templateBackend->setData('orig_template_code', $templateOption['value']);
                    //need if customer template with (int) id
                    $templateBackend->setData(
                        'template_variables',
                        $templateBackend->getVariablesOptionArray()
                    );

                    $templateText = $templateBackend->getData('template_text');
                    $content = $this->variable->filter($templateText);
                    $templateName = (string)$templateOption['label'];
                    $templateCode = \Cordial\Sync\Model\Api\Config::API_EL_PREF . str_replace('/', '_', $templateOption['value']);
                    $api = $this->api->load($storeId);
                    $res = $api->createTemplate($templateName, $templateCode, $content);
                    if (!$res) {
                        $templateError[] = $templateOption['value'] . ' :: ' . $templateCode;
                    }
                }

                $templateData = $this->_objectManager->create('Cordial\Sync\Model\Template')->loadByOrigCode($templateOption['value'], $storeId);
                $template = $this->_objectManager->create('Cordial\Sync\Model\Template');
                if (isset($templateData['template_id'])) {
                    $template->load($templateData['template_id']);
                }

                $template->setData('template_code', $templateCode);
                $template->setData('orig_template_code', $templateOption['value']);
                $template->setData('store_id', $storeId);
                $template->save();
            }
            if (empty($templateError)) {
                $result = ['status' => 'success', 'sync' => true];
            } else {
                $this->logger->error('Issue with templates ', $templateError);
                $result = ['status' => 'error'];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $result = ['status' => 'error'];
        } catch (\Exception $e) {
            $result = ['status' => 'error'];
            $this->logger->critical($e);
        }

        return $this->jsonResponse($result);
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
}
