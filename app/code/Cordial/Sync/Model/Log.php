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

namespace Cordial\Sync\Model;

use Cordial\Sync\Api\Data\LogInterface;

class Log extends \Magento\Framework\Model\AbstractModel implements LogInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Cordial\Sync\Model\ResourceModel\Log::class);
    }

    /**
     * Get log_id
     * @return string
     */
    public function getLogId()
    {
        return $this->getData(self::LOG_ID);
    }

    /**
     * Set log_id
     * @param string $logId
     * @return \Cordial\Sync\Api\Data\LogInterface
     */
    public function setLogId($logId)
    {
        return $this->setData(self::LOG_ID, $logId);
    }
}
