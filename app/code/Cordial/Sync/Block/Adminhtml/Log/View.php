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

namespace Cordial\Sync\Block\Adminhtml\Log;

class View extends \Magento\Backend\Block\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
    
        $this->coreRegistry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve log id
     *
     * @return string|null
     */
    public function getLog()
    {
        return $this->coreRegistry->registry('cordial_sync_log');
    }
}
