<?php

namespace Scommerce\GlobalSiteTag\Block\Adminhtml\Form\Field;

use \Magento\Framework\View\Element\Html\Select;
use \Magento\Framework\View\Element\Context;
use \Magento\Config\Model\Config\Source\Yesno;


class YesNoField extends Select
{
    /**
     * Model Yesno
     *
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * Activation constructor.
     *
     * @param Context $context
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        Context $context,
        Yesno $yesNo,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_yesNo = $yesNo;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Parse to html.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $attributes = $this->_yesNo->toOptionArray();

            foreach ($attributes as $attribute) {
                $this->addOption($attribute['value'], $attribute['label']);
            }
        }

        return parent::_toHtml();
    }
}