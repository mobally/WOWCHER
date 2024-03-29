<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_AdminActionsLog
 */


namespace Amasty\AdminActionsLog\Block\Adminhtml\ActionsLog;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_registryManager;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_registryManager = $coreRegistry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_controller = 'adminhtml_actionsLog';
        $this->_blockGroup = 'Amasty_AdminActionsLog';

        parent::_construct();

        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');

        if ($this->_isRestorable()) {
            $log = $this->_registryManager->registry('amaudit_actionslog');
            $storeview = $log ? $log->getStoreId() : '0';
            $message = addslashes(
                $this->_scopeConfig
                    ->getValue(
                        'amaudit/restore/restore_popup_message',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                        $storeview
                    )
            );

            $this->addButton(
                'restore_changes',
                [
                    'label' => __('Restore Changes'),
                    'class' => 'restore_changes',
                    'onclick' => "confirmSetLocation('{$message}', '{$this->_getRestoreUrl()}')"
                ]
            );
        }
    }

    protected function _getRestoreUrl()
    {
        return $this->getUrl('amaudit/actionslog/restore', ['log_id' => $this->getRequest()->getParam('id')]);
    }

    protected function _isRestorable()
    {
        $isRestorable = true;
        $notRestorableCategories = ['sales/order_create'];

        $log = $this->_registryManager->registry('amaudit_actionslog');

        if ($log->getType() != 'Edit'
            || in_array($log->getCategory(), $notRestorableCategories)
        ) {
            $isRestorable = false;
        }

        return $isRestorable;
    }
}
