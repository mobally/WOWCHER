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

namespace Cordial\Sync\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ACTIVE = 'cordial_sync/general/active';
    const XML_PATH_ROUTE = 'cordial_sync/general/route';
    const XML_PATH_API_KEY = 'cordial_sync/general/api_key';
    const XML_PATH_ACCOUNT_KEY = 'cordial_sync/general/account_key';
    const XML_PATH_ENABLE_TRACK_JSV2 = 'cordial_sync/general/enable_jsv2';
    const XML_PATH_JS_LISTENER = 'cordial_sync/general/js_listener';
    const XML_PATH_CUSTOMER_ATTRIBUTES_MAP = 'cordial_sync/general/customer_attributes_map';
    const ENTITY_TYPE_PRODUCT = 'catalog_product';
    const CORDIAL_VARS = 'cordial_vars';

    const STEP = 10;

    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfig(self::XML_PATH_ACTIVE, $storeId);
    }

    public function isRoute($storeId = null)
    {
        return (bool)$this->getConfig(self::XML_PATH_ROUTE, $storeId);
    }

    public function isLoggingEnabled($storeId = null)
    {
        return true;
    }

    public function syncImmediately()
    {
        return (bool)\Cordial\Sync\Model\Api\Config::SYNC_IMMEDIATELY;
    }

    public function getSyncAttrCode()
    {
        return \Cordial\Sync\Model\Api\Config::ATTR_CODE;
    }

    public function log($message)
    {
        $this->_logger->error($message);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->_logger->debug($message);
    }

    public function getApiKey($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_API_KEY, $storeId);
    }

    public function getAccountKey($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_ACCOUNT_KEY, $storeId);
    }

    public function getJsV2Enabled($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_ENABLE_TRACK_JSV2, $storeId);
    }

    public function getJsLoader($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_JS_LISTENER, $storeId);
    }

    /**
     * Returns config value
     *
     * @param string $key
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\App\Config\Element
     */
    public function getConfig($key, $store = null)
    {
        return $this->scopeConfig->getValue(
            $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function convertAttributeType($attribute)
    {
        switch ($attribute['frontend_input']) {
            case 'date':
                $type = 'date';
                break;
            case 'decimal':
                $type = 'number';
                break;
        }

        if ($attribute['attribute_code'] == 'default_billing' || $attribute['attribute_code'] == 'default_shipping') {
            $type = 'geo';
        }

        if (!isset($type)) {
            switch ($attribute['frontend_input']) {
                case 'text':
                case 'select':
                    $type = 'string';
                    break;
                default:
                    $type = 'string';
            }
        }

        return $type;
    }
}
