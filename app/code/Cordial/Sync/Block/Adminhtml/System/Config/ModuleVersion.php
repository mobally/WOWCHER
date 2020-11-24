<?php

namespace Cordial\Sync\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Cordial\Sync\Model\Config as ModelConfig;

class ModuleVersion extends Field
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_template = 'Cordial_Sync::system/config/module_version.phtml';

    /**
     */
    private $modelConfig;

    /**
     * @param  Context     $context
     * @param  array       $data
     */
    public function __construct(
        Context $context,
        ModelConfig $modelConfig,
        array $data = []
    ) {
        $this->modelConfig  = $modelConfig;
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->modelConfig->getModuleVersion();
    }
}
