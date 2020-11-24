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

namespace Cordial\Sync\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

class SyncButton extends Field
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->setTemplate('Cordial_Sync::sync_button.phtml');
        return parent::_toHtml();
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'cordial_sync_button',
                'label' => __('Post Data to Cordial')
            ]
        );

        return $button->toHtml();
    }

    public function getStoreId()
    {
        return $this->getRequest()->getParam('store');
    }
}
