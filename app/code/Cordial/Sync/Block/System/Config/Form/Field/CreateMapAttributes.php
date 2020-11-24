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

class CreateMapAttributes extends Field
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->setTemplate('Cordial_Sync::map_attributes.phtml');
        return parent::_toHtml();
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
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
                'label' => __('Send New Attributes to Cordial'),
                'onclick' => 'createMapAttributes()'
            ]
        )->toHtml();

        return $html . $button;
    }

    /**
     * Return ajax url for create map button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        $storeId = (int)$this->_request->getParam('store', 0);
        return $this->getUrl('cordial_sync/map/save', ['store' => $storeId]);
    }
}
