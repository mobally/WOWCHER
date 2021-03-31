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

class Config
{
    const ENDPOINT = 'api.cordial.io';

    //const ENDPOINT_DEV = 'magento.api.dev.cordial.io';
    const ENDPOINT_DEV = 'api.magento.dev.cordialdev.com';
    const VERSION = 'v1';
    const SCHEME      = 'http';
    const DATE_FORMAT = 'Y-m-d H:i:sO';
    const TIMEOUT = 10;
    const SYNC_STEP_SIZE = 40; // sync 50 products, orders, ... per step
    const SYNC_STEP_SIZE_S = 10; // sync 10 products, orders, ... per step
    const SYNC_RECENT_PERIOD = '-90 days';
    const ATTR_CODE = 'cordial_sync';
    const SYNC_IMMEDIATELY = false;
    const API_EL_PREF = 'm_';
}
