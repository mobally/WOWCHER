<?php
namespace Scommerce\GlobalSiteTag\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class AccountId
 */
class AccountId extends AbstractFieldArray
{
    /**
     * @var
     */
    protected $useLinker;

    /**
     * @var
     */
    protected $mainAccount;

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'account_id',
            [
                'label' => __('Account Id'),
                'class' => 'required-entry'
            ]
        );
        $this->addColumn(
            'main_account',
            [
                'label' => __('Main account'),
                'renderer' => $this->getMainAccountRenderer()
            ]
        );
        $this->addColumn(
            'use_linker',
            [
                'label' => __('Use Linker'),
                'renderer' => $this->getUseLinkerRenderer()
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Account');
    }

    /**
     * Get useLinker options.
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function getUseLinkerRenderer()
    {
        if (!$this->useLinker) {
            $this->useLinker = $this->getLayout()->createBlock(
                '\Scommerce\GlobalSiteTag\Block\Adminhtml\Form\Field\YesNoField',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->useLinker;
    }

    /**
     * Get mainAccount options.
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function getMainAccountRenderer()
    {
        if (!$this->mainAccount) {
            $this->mainAccount = $this->getLayout()->createBlock(
                '\Scommerce\GlobalSiteTag\Block\Adminhtml\Form\Field\YesNoField',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->mainAccount;
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];

        $customAttribute = $row->getData('use_linker');
        $key = 'option_' . $this->getUseLinkerRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';

        $customAttribute = $row->getData('main_account');
        $key = 'option_' . $this->getMainAccountRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }
}