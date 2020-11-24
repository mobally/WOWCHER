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

namespace Cordial\Sync\Model\System;

use Magento\Framework\Data\OptionSourceInterface;

class Status extends \Magento\Framework\DataObject implements OptionSourceInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => \Cordial\Sync\Model\Touched::STATUS_SYNC, 'label' => __('Sync')],
            ['value' => \Cordial\Sync\Model\Touched::STATUS_UNSYNC, 'label' => __('UnSync')],
        ];

        return $options;
    }
}
