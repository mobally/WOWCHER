<?php
namespace HP\OrderGrid\Block\Adminhtml\Product\Grid\Renderer;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;

    protected $_logo;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_logo = $logo;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $img;
        $aa = $this->_logo->getLogoSrc();
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        if ($this->_getValue($row)!=''):
            $imageUrl = $mediaDirectory.'catalog/product/'.$this->_getValue($row);
            $img='<img src="'.$imageUrl.'" width="50" height="50"/>';
        else:
            $img='<img src="'.$aa.'" width="50" height="50"/>';
        endif;
        return $img;
    }
}
