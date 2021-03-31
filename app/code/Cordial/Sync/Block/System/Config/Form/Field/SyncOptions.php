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

class SyncOptions extends Field
{
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
     * @return string
     */
    protected function _toHtml()
    {
        $this->setTemplate('Cordial_Sync::sync_options.phtml');
        return parent::_toHtml();
    }

    public function getValues()
    {
        $values = [];
        $values['products'] = __('Products');
        $values['customers'] = __('Customers');
        $values['orders'] = __('All Orders');
        $values['orders_recent'] = __('Recent Orders (last 90 days)');
        $values['templates'] = __('Templates');
        return $values;
    }
}
