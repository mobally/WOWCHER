<?php

namespace Rvs\VirtualProduct\Block\Adminhtml\Voucher\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var array
     */
    protected $_sessionData = null;

    /**
     * @var array
     */
    protected $_allStores = null;

    /**
     * @var bool
     */
    protected $_requireStoresFilter = false;

    /**
     * @var array
     */
    protected $_formValues = [];

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $_voucherFactory;

    /**
     * @var \Rvs\VirtualProduct\Model\Voucher\Source\Status
     */
    protected $statusProvider;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Rvs\VirtualProduct\Model\Voucher\Source\Status $statusProvider
     * @param \Rvs\VirtualProduct\Model\VoucherFactory $voucherFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Rvs\VirtualProduct\Model\Voucher\Source\Status $statusProvider,
        \Rvs\VirtualProduct\Model\VoucherFactory $voucherFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Helper\Data $adminhtmlData,
        array $data = []
    ) {
        $this->statusProvider   = $statusProvider;
        $this->_voucherFactory  = $voucherFactory;
        $this->_systemStore     = $systemStore;
        $this->_adminhtmlData   = $adminhtmlData;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set form id and title
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('voucher_form');
        $this->setTitle(__('Block Information'));
    }

    /**
     * Initialize form values
     * Set form data either from model values or from session
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $model = $this->_getModel();
        $this->_formValues = [
            'voucher_id'    => $model->getId(),
            'sku'           => $model->getSku(),
            'child_sku'     => $model->getChildSku(),
            'voucher_code'  => $model->getVoucherCode(),
            'url'           => $model->getUrl(),
            'order_id'      => $model->getOrderId(),
            'status'        => $model->getStatus(),
            'expiration_date'   => $model->getExpirationDate()
        ];

        $sessionData = $this->_getSessionData();
        if ($sessionData) {
            foreach (array_keys($this->_formValues) as $key) {
                if (isset($sessionData[$key])) {
                    $this->_formValues[$key] = $sessionData[$key];
                }
            }
        }

        return $this;
    }

    /**
     * Prepare the form layout
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $this->_initFormValues();

        // Prepare form
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'use_container' => true, 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Voucher Information')]);

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'disabled' => true,
                'options' => $this->statusProvider->toArray(),
                'value' => $this->_formValues['status']
            ]
        );

        $fieldset->addField(
            'voucher_id',
            'hidden',
            [
                'name' => 'voucher_id',
                'value' => $this->_formValues['voucher_id']
            ]
        );

        $fieldset->addField(
            'order_id',
            'hidden',
            [
                'name' => 'order_id',
                'value' => $this->_formValues['order_id']
            ]
        );

        $fieldset->addField(
            'sku',
            'text',
            [
                'label' => __('SKU'),
                'title' => __('SKU'),
                'name' => 'sku',
                'required' => true,
                'value' => $this->_formValues['sku']
            ]
        );

        $fieldset->addField(
            'child_sku',
            'text',
            [
                'label' => __('Child SKU'),
                'title' => __('Child SKU'),
                'name' => 'child_sku',                
                'value' => $this->_formValues['child_sku']
            ]
        );

        $fieldset->addField(
            'voucher_code',
            'text',
            [
                'label' => __('Voucher Code'),
                'title' => __('Voucher Code'),
                'name' => 'voucher_code',
                'required' => true,
                'value' => $this->_formValues['voucher_code']
            ]
        );

        $fieldset->addField(
            'expiration_date',
            'text',
            [
                'label' => __('Expiration Date'),
                'title' => __('Expiration Date'),
                'name' => 'expiration_date',
                'required' => true,
                'value' => $this->_formValues['expiration_date']
            ]
        );

        //$this->_prepareStoreElement($fieldset);

        $fieldset->addField(
            'url',
            'text',
            [
                'label' => __('Virtual Product Url'),
                'title' => __('Virtual Product Url'),
                'name' => 'url',
                'required' => true,
                'value' => $this->_formValues['url']
            ]
        );

        /* $fieldset->addField(
            'order_id',
            'text',
            [
                'label' => __('Order #'),
                'title' => __('Order #'),
                'name' => 'order_id',                
                'disabled' => true,
                'value' => $this->_formValues['order_id']
            ]
        ); */

        $this->setForm($form);
        $this->_formPostInit($form);

        return parent::_prepareForm();
    }

    /**
     * Form post init
     *
     * @param \Magento\Framework\Data\Form $form
     * @return $this
     */
    protected function _formPostInit($form)
    {
        $form->setAction(
            $this->_adminhtmlData->getUrl('*/*/save', ['voucher_id' => $this->_getModel()->getId()])
        );
        return $this;
    }

    /**
     * Get session data
     *
     * @return array
     */
    protected function _getSessionData()
    {
        if ($this->_sessionData === null) {
            $this->_sessionData = $this->_backendSession->getData('voucher_data', true);
        }
        return $this->_sessionData;
    }

    /**
     * Get URL rewrite model instance
     *
     * @return \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected function _getModel()
    {
        if (!$this->hasData('voucher')) {
            $this->setVoucher($this->_voucherFactory->create());
        }
        return $this->getVoucher();
    }
}
