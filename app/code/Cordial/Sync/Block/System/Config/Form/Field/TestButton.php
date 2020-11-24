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

class TestButton extends Field
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->setTemplate('Cordial_Sync::test_button.phtml');
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
        $html = $this->_toHtml();
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => $element->getHtmlId(),
                'name' => $element->getName(),
                'label' => __('Test Templates'),
                'onclick' => 'testTemplates();'
            ]
        )->toHtml();

        return $html . $button;
    }

    /**
     * Return url for test button
     *
     * @return string
     */
    public function getTestUrl()
    {
        return $this->getUrl('sync/test/index');
    }
}
