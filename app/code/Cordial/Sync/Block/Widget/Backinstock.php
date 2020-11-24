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

namespace Cordial\Sync\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Backinstock extends Template implements BlockInterface
{

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockState;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Cordial\Sync\Helper\Data
     */
    protected $helper;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Cordial\Sync\Helper\Data $helper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Cordial\Sync\Helper\Data $helper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->helper = $helper;
        $this->stockRegistry = $stockRegistry;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->setTemplate('Cordial_Sync::widget/backinstock.phtml');
        return parent::_toHtml();
    }

    public function canShow()
    {
        $storeManager = $this->objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
        $storeId = $storeManager->getStore()->getId();
        if (!$this->helper->isEnabled($storeId)) {
            return false;
        }
        $currentProduct = $this->registry->registry('current_product');
        if ($this->stockRegistry->getProductStockStatus($currentProduct->getId())) {
            return false;
        }

        return true;
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('cordial_sync/index/subscribe');
    }

    public function getProductId()
    {
        $currentProduct = $this->registry->registry('current_product');
        if ($currentProduct instanceof \Magento\Catalog\Model\Product) {
            return $currentProduct->getId();
        }
        return 0;
    }
}
