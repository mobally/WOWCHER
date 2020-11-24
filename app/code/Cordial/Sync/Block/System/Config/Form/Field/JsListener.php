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

// @codingStandardsIgnoreFile

namespace Cordial\Sync\Block\System\Config\Form\Field;

class JsListener extends \Magento\Config\Block\System\Config\Form\Field
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
        $html = $element->getElementHtml();
        $html .= $this->getButtonHtml();
        $html .= '<textarea id="default_js_listener_code" style="display: none;"><script>
    requirejs([], function() {
        var t = document.createElement("script");
        t.setAttribute("data-cordial-track-key", "$accountkey");
        t.setAttribute("data-cordial-url", "track.cordial.io");
        t.setAttribute("data-auto-track", false);
        t.src = \'//track.cordial.io/track.js\';
        t.async = true;
        t.onload = cordialMagento;
        document.body.appendChild(t);
    });
</script></textarea>';

        $html .= "<script type=\"text/javascript\">
                //<![CDATA[
                function defaultListenerCode(){
                    $('cordial_sync_general_js_listener').setValue($('default_js_listener_code').getValue());
                    alert('Click Save Config to save settings.');
                }
                //]]>
                </script>";
        return $html;
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'default_js_button',
                'label' => __('Reset to Default Javascript'),
                'onclick' => 'defaultListenerCode();',
                'style' => 'margin-top:10px'
            ]
        );

        return $button->toHtml();
    }
}