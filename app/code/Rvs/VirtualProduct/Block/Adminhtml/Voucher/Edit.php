<?php

namespace Rvs\VirtualProduct\Block\Adminhtml\Voucher;

class Edit extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Part for building some blocks names
     *
     * @var string
     */
    protected $_controller = 'rvs_virtualproduct';

    /**
     * Generated buttons html cache
     *
     * @var string
     */
    protected $_buttonsHtml;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\UrlRewrite\Model\UrlvoucherFactory
     */
    protected $_voucherFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Rvs\VirtualProduct\Model\VoucherFactory $voucherFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Rvs\VirtualProduct\Model\VoucherFactory $voucherFactory,
        \Magento\Backend\Helper\Data $adminhtmlData,
        array $data = []
    ) {
        $this->_voucherFactory = $voucherFactory;
        $this->_adminhtmlData = $adminhtmlData;
        parent::__construct($context, $data);
    }

    /**
     * Prepare URL rewrite editing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Rvs_VirtualProduct::edit.phtml');

        $this->_addBackButton();
        $this->_prepareLayoutFeatures();

        return parent::_prepareLayout();
    }

    /**
     * Prepare featured blocks for layout of URL rewrite editing
     *
     * @return void
     */
    protected function _prepareLayoutFeatures()
    {
        if ($this->_getVoucher()->getVoucherId()) {
            $this->_headerText = __('Edit Voucher');
        } else {
            $this->_headerText = __('Add New Voucher');
        }
        
        $this->_addEditFormBlock();
    }

    /**
     * Add child edit form block
     *
     * @return void
     */
    protected function _addEditFormBlock()
    {
        $this->setChild('form', $this->_createEditFormBlock());

        if ($this->_getVoucher()->getVoucherId()) {
            $this->_addResetButton();
            //$this->_addDeleteButton();
        }

        $this->_addSaveButton();
    }

    /**
     * Add reset button
     *
     * @return void
     */
    protected function _addResetButton()
    {
        $this->addButton(
            'reset',
            [
                'label' => __('Reset'),
                'onclick' => 'location.reload();',
                'class' => 'scalable',
                'level' => -1
            ]
        );
    }

    /**
     * Add back button
     *
     * @return void
     */
    protected function _addBackButton()
    {
        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->_adminhtmlData->getUrl('*/*/') . '\')',
                'class' => 'back',
                'level' => -1
            ]
        );
    }

    /**
     * Update Back button location link
     *
     * @param string $link
     * @return void
     */
    protected function _updateBackButtonLink($link)
    {
        $this->updateButton('back', 'onclick', 'setLocation(\'' . $link . '\')');
    }

    /**
     * Add delete button
     *
     * @return void
     */
    protected function _addDeleteButton()
    {
        $this->addButton(
            'delete',
            [
                'label' => __('Delete'),
                'onclick' => 'deleteConfirm(' . json_encode(__('Are you sure you want to do this?'))
                    . ','
                    . json_encode(
                        $this->_adminhtmlData->getUrl(
                            '*/*/delete',
                            ['voucher_id' => $this->getVoucher()->getId()]
                        )
                    )
                    . ', {data: {}})',
                'class' => 'scalable delete',
                'level' => -1
            ]
        );
    }

    /**
     * Add save button
     *
     * @return void
     */
    protected function _addSaveButton()
    {
        $this->addButton(
            'save',
            [
                'label' => __('Save'),
                'class' => 'save primary save-voucher',
                'level' => -1,
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ]
            ]
        );
    }

    /**
     * Creates edit form block
     *
     * @return \Magento\UrlRewrite\Block\Edit\Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            \Rvs\VirtualProduct\Block\Adminhtml\Voucher\Edit\Form::class,
            '',
            ['data' => ['voucher' => $this->_getVoucher()]]
        );
    }

    /**
     * Get container buttons HTML
     *
     * Since buttons are set as children, we remove them as children after generating them
     * not to duplicate them in future
     *
     * @param string|null $area
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getButtonsHtml($area = null)
    {
        if (null === $this->_buttonsHtml) {
            $this->_buttonsHtml = parent::getButtonsHtml();
            $layout = $this->getLayout();
            foreach ($this->getChildNames() as $name) {
                $alias = $layout->getElementAlias($name);
                if (false !== strpos($alias, '_button')) {
                    $layout->unsetChild($this->getNameInLayout(), $alias);
                }
            }
        }
        return $this->_buttonsHtml;
    }

    /**
     * Get or create new instance of URL rewrite
     *
     * @return \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected function _getVoucher()
    {
        if (!$this->hasData('voucher')) {            
            $this->setVoucher($this->_voucherFactory->create());
        }        
        return $this->getVoucher();
    }
}
