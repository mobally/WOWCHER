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

namespace Cordial\Sync\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class Events implements SectionSourceInterface
{
    /**
     * @var \Cordial\Sync\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Cordial\Sync\Helper\Data $helper
     */
    public function __construct(
        \Cordial\Sync\Helper\Data $helper
    ) {
    
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $data = $this->getJsEvent();
        $data .= "\n";
        $data .= '<script type="text/javascript">cordialMagento()</script>';
        return ['data' => $data];
    }

    /**
     * Returns parameters that should appear in the loader code
     *
     * @return string
     */
    public function getJsEvent()
    {
        $res = $this->_helper->getJsEvent();
        return $res;
    }
}
