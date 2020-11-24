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

namespace Cordial\Sync\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ApiFactory
 */
class ApiFactory
{
    const API_PRODUCT = 'catalog_product';
    const API_CUSTOMER = \Magento\Customer\Model\Customer::ENTITY;
    const API_ORDER = 'order';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $apiMap = [
        self::API_PRODUCT => \Cordial\Sync\Model\Api\Product::class,
        self::API_CUSTOMER => \Cordial\Sync\Model\Api\Customer::class,
        self::API_ORDER => \Cordial\Sync\Model\Api\Order::class
    ];

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create api
     *
     * @param string $key
     * @return \Cordial\Sync\Model\Api\Product|\Cordial\Sync\Model\Api\Customer|Cordial\Sync\Model\Api\Order
     * @throws LocalizedException
     */
    public function create($key)
    {
        if (!isset($this->apiMap[$key])) {
            throw new LocalizedException(__(sprintf('<%s> No found api <%s>', $key)));
        }

        return $this->objectManager->get($this->apiMap[$key]);
    }
}
