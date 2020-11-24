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

class Attribute extends Client
{

    /**
     * Get all attributes
     *
     * @param int|Mage_Customer_Model_Customer $customer
     * @return boolean
     */
    public function getAllAttributes()
    {
        $path = "accountcontactattributes";
        $data = [
            'page' => 1,
            'per_page' => 10000
        ];
        $result = $this->_request('GET', $path, $data);
        if (empty($result)) {
            return [];
        }

        return $result;
    }

    /**
     * Create Attribute
     *
     */
    public function create($attribute, $attrType, $attrOption)
    {
        $path = "accountcontactattributes";
        $options = [];

        if ($attrType == 'attribute') {
            switch ($attribute->getBackendType()) {
                case 'datetime':
                    $type = 'date';
                    break;
                case 'decimal':
                    $type = 'number';
                    break;
            }

            if ($attribute->getBackendModel() == \Magento\Customer\Model\Customer\Attribute\Backend\Billing::class
                || $attribute->getBackendModel() == \Magento\Customer\Model\Customer\Attribute\Backend\Shipping::class) {
                $type = 'geo';
            }

            if (!isset($type)) {
                switch ($attribute->getFrontendInput()) {
                    case 'text':
                    case 'select':
                        $type = 'string';
                        break;
                    default:
                        $type = 'string';
                }
            }

            $data = [
                'key' => $attrOption,
                'name' => $attribute->getFrontendLabel(),
                'type' => $type,
                'index' => true,
                'options' => $options
            ];
        }

        if ($attrType == 'customAttribute') {
            $data = [
                'key' => $attribute['value'],
                'name' => $attrOption,
                'type' => $attribute['type'],
                'index' => true,
                'options' => $options
            ];
        }
        $result = $this->_request('POST', $path, $data);
        return $result;
    }

    /**
     * Create List
     *
     */
    public function createList($listName)
    {
        $path = "accountlists";
        $data = [
            'name' => $listName,
            'enhanced'=> true
        ];
        $result = $this->_request('POST', $path, $data);
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Get lists
     *
     * @return boolean
     */
    public function getAccountlists($storeId = null, $name = null)
    {
        $result = [];
        $data = [];
        $path = "accountlists";
        if (!is_null($name)) {
            $data = [
                'name' => $name
            ];
        }

        $result = $this->_request('GET', $path, $data);
        return $result;
    }
}
