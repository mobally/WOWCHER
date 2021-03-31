<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_AdminActionsLog
 */


namespace Amasty\AdminActionsLog\Observer;

use Amasty\AdminActionsLog\Helper\Data;
use Amasty\AdminActionsLog\Model\Log;
use Amasty\AdminActionsLog\Model\LogDetails;
use Magento\Downloadable\Model\Link as DownloadableLink;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;

class HandleModelSaveAfter implements ObserverInterface
{
    protected $arrayKeysToString = ['associated_product_ids'];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var Registry
     */
    protected $registryManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Registry $coreRegistry,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        State $appState
    ) {
        $this->objectManager = $objectManager;
        $this->registryManager = $coreRegistry;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                $object = $observer->getObject();

                if ($this->helper->needToSave($object)) {
                    $this->_saveLog($object);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            null;// no action is Area Code in not set
        }
    }

    protected function _saveLog($object)
    {
        $possibleOrderClasses = [
            \Magento\CustomerCustomAttributes\Model\Sales\Quote::class,
            \Magento\CustomerCustomAttributes\Model\Sales\Quote\Address::class
        ];

        if (!$this->registryManager->registry('amaudit_log_saved')
            || $this->_isMassAction()
        ) {
            /** @var Log $logModel */
            $logModel = $this->objectManager->create(Log::class);
            $data = $logModel->prepareLogData($object);

            if (!isset($data['username'])
                || (in_array(get_class($object), $possibleOrderClasses) && !isset($data['item']))
            ) {
                return;
            }

            $logModel->addData($data);
            $logModel->save();
            $this->registryManager->register('amaudit_log_saved', $logModel, true);
        } else {
            /** @var Log $logModel */
            $logModel = $this->registryManager->registry('amaudit_log_saved');

            if ($this->helper->isCompletedOrder($object, $logModel)
            ) {
                $data = $logModel->prepareLogData($object);
                $logModel->setType('New');
                $logModel->setData($data);
                $logModel->save();
            } elseif ($object instanceof \Magento\Catalog\Model\Product) {
                $logModel->setData('item', $object->getName());
                $logModel->save();
            }
        }

        if (!$this->isNeedLogOrderInfo($object)) {
            return;
        }

        $this->saveLogDetails($object, $logModel);
    }

    protected function isNeedLogOrderInfo($object)
    {
        $needToLog = true;

        $unnescesaryClasses = [
            \Magento\CustomerCustomAttributes\Model\Sales\Order\Address::class,
            \Magento\CustomerCustomAttributes\Model\Sales\Order::class,
            \Magento\Sales\Model\Order\Interceptor::class,
            \Magento\Sales\Model\Order\Status\History::class
        ];

        if ($this->registryManager->registry('order_info_saved')
            && in_array(get_class($object), $unnescesaryClasses)
        ) {
            $needToLog = false;
        }

        return $needToLog;
    }

    protected function _isMassAction()
    {
        $isMassAction = false;

        $massActions = [
            'massDisable',
            'massEnable',
            'inlineEdit',
            'massHold',
            'massUnhold'
        ];

        $action = $this->registryManager->registry('amaudit_action');

        if (in_array($action, $massActions)) {
            $isMassAction = true;
        }

        return $isMassAction;
    }

    protected function saveLogDetails($object, $logModel)
    {
        $isConfig = $object instanceof \Magento\Framework\App\Config\Value;

        $orderClassesToLog = [
            \Magento\Sales\Model\Order\Shipment::class ,
            \Magento\Sales\Model\Order\Invoice::class,
            \Magento\Sales\Model\Order\Creditmemo\Interceptor::class,
            \Magento\Sales\Model\Order\Status\History::class
        ];

        if ($isConfig) {
            $path = $object->getPath();
            $newData[$path] = $object->getValue();
            $oldData[$path] = $this->scopeConfig->getValue($path);
        } else {
            $newData = $object->getData();

            switch (true) {
                case $object instanceof \Magento\Catalog\Model\Product\Option:
                    $oldData = $this->getOldProductOptionData($newData);
                    unset($oldData['values']);
                    unset($newData['values']);

                    break;
                case $object instanceof \Magento\Catalog\Model\Product\Option\Value:
                    $oldData = $this->getOldProductOptionValueData($newData);
                    break;
                case $object instanceof \Magento\Bundle\Model\Option:
                    $oldData = $this->getOldProductOptionData($newData, true);
                    break;
                default:
                    $oldData = $object->getOrigData();
                    if ($this->helper->needOldData($object)) {
                        $oldDataBeforeSave = $this->registryManager->registry('amaudit_data_before');

                        if (is_array($oldData)) {
                            $oldData = $oldData + $oldDataBeforeSave;
                        } else {
                            $oldData = $oldDataBeforeSave;
                        }
                    }
            }
        }

        $typeLog = $logModel->getType();

        if (!$this->registryManager->registry('order_info_saved')
            && in_array(get_class($object), $orderClassesToLog)
        ) {
            $this->registryManager->register('order_info_saved', true);
        }

        if ($typeLog == 'New' && !$isConfig) {
            foreach ($newData as $key => $value) {
                $this->_saveOneDetail($logModel, $object, $key, '', $newData[$key]);
            }
        }

        if (is_array($oldData)) {
            foreach ($oldData as $key => $value) {
                if ($typeLog != 'New' || $isConfig) {
                    $newKey = $this->_changeNewKey($key, $logModel->getCategory());

                    if (array_key_exists($newKey, $newData)) {
                        $this->_saveOneDetail($logModel, $object, $key, $oldData[$key], $newData[$newKey]);
                    }
                }
            }
        }
    }

    protected function getOldProductOptionData(array $newData, $isBundle = false): array
    {
        if ($isBundle) {
            $options = $this->registryManager->registry('amaudit_bundle_product_options_before');
        } else {
            $options = $this->registryManager->registry('amaudit_product_options_before');
        }

        $data = [];

        if (isset($newData['option_id'], $options[$newData['option_id']])) {
            $data = $options[$newData['option_id']];
        }

        return $data;
    }

    private function getOldProductOptionValueData(array $newValueData): array
    {
        $data = [];
        $valueId = $newValueData['option_type_id'];

        if ($optionData = $this->getOldProductOptionData($newValueData)) {
            if (isset($optionData['values'], $optionData['values'][$valueId])) {
                $data = $optionData['values'][$valueId];
            }
        }

        return $data;
    }

    // phpcs:ignore Generic.Metrics.NestingLevel.TooHigh
    protected function _saveOneDetail($logModel, $object, $key, $oldValue, $newValue)
    {
        $saveArrayAsString = [
            'website_ids',
            'store_id',
            'category_ids',
        ];
        $keysNotForLogging = [
            '_cache_instance_product_set_attributes',
            '_cache_instance_configurable_attributes',
            '_cache_editable_attributes',
            'extension_attributes',
            'updated_at',
            'form_key',
            'quantity_and_stock_status',
            'new_variations_attribute_set_id',
            'new_variations_attribute_set_id',
            'type_has_options',
            'type_has_required_options',
            'can_save_bundle_selections',
            'can_save_configurable_attributes',
            'stock_data'
        ];

        $keyNotForSaving = [
            '0'
        ];

        switch (true) {
            case $object instanceof \Magento\Catalog\Model\Product:
                $keyNotForSaving[] = 'options';
                break;
            case $object instanceof \Magento\Catalog\Model\Product\Option:
                $keyNotForSaving = [
                    'default_title',
                    'store_title',
                    'store_price',
                    'default_price'
                ];
                break;
            case $object instanceof \Magento\Bundle\Model\Option:
                $keyNotForSaving = [
                    'selection_can_change_qty',
                    'product_links'
                ];
                break;
        }

        if (!in_array($key, $keyNotForSaving)) {
            $keysAlwaysSave = [
                'comment',
            ];

            if (in_array($key, $keysAlwaysSave)) {
                $oldValue = '';
            }

            if ($oldValue instanceof \DateTime) {
                $oldValue = $oldValue->format('Y-m-d H:i:s');
            }

            if ($newValue instanceof \DateTime) {
                $newValue = $newValue->format('Y-m-d H:i:s');
            }

            if (strpos($key, 'password') !== false) {
                $stars = '*****';
                $newValue = $stars;

                if (!empty($oldValue)) {
                    $oldValue = $stars;
                }
            }

            if (in_array($key, $this->arrayKeysToString, true)) {
                if (is_array($oldValue)) {
                    $oldValue = implode(',', $oldValue);
                } else {
                    $oldValue = (string)$oldValue;
                }
                if (is_array($newValue)) {
                    $newValue = implode(',', $newValue);
                } else {
                    $newValue = (string)$newValue;
                }
            }

            if (is_string($newValue) && is_string($oldValue)) {
                $oldValue = str_replace("\r\n", "\n", $oldValue);
                $newValue = str_replace("\r\n", "\n", $newValue);
            }

            switch ($this->outerConditionResolver($key, $keysNotForLogging, $oldValue, $newValue, $saveArrayAsString)) {
                case 'isMultipleIdsInstance':
                    if (is_array($newValue)) {
                        $newValue = implode(',', $newValue);
                    }

                    $this->_saveOneDetail($logModel, $object, $key, implode(',', $oldValue), $newValue);
                    break;
                case 'isSimpleInstance':
                    if (get_class($object) == DownloadableLink::class) {
                        unset($oldValue['product']);
                    }

                    foreach ($oldValue as $k => $v) {
                        switch ($this->innerConditionResolver($v, $k, $keysNotForLogging, $newValue)) {
                            case 'recursiveDataCall':
                                $this->_saveOneDetail($logModel, $v, $k, $v->getData(), $newValue[$k]->getData());
                                break;
                            case 'recursiveCallForArray':
                                $this->_saveOneDetail($logModel, $object, $k, $v, $newValue[$k]);
                                break;
                            case 'recursiveCallForString':
                                $this->_saveOneDetail($logModel, $object, $k, $v, (string)$newValue);
                                break;
                        }
                    }
                    break;
                case 'recursiveDataCall':
                    $this->_saveOneDetail($logModel, $oldValue, $key, $oldValue->getData(), $newValue->getData());
                    break;
                case 'notDeleted':
                    $typeLog = $logModel->getType();
                    $logDetailsModel = $this->objectManager->get(LogDetails::class);

                    if ($typeLog == 'Edit') {
                        $newKey = $this->_changeNewKey($key, $logModel->getCategory());
                    } else {
                        $newKey = $key;
                    }

                    $data = [];
                    $data['log_id'] = $logModel->getId();
                    $data['new_value'] = $this->_prepareNewData($newKey, $newValue);
                    $data['name'] = $key;
                    $data['model'] = get_class($object);
                    $data['old_value'] = $this->_prepareOldData($key, $oldValue);

                    if ($data['old_value'] != $data['new_value']) {
                        $logDetailsModel->setData($data);
                        $logDetailsModel->save();
                    }
                    break;
            }
        }
    }

    protected function outerConditionResolver($key, $keysNotForLogging, $oldValue, $newValue, $saveArrayAsString)
    {
        if (!in_array($key, $keysNotForLogging) || is_int($key)) {
            if (is_array($oldValue)) {
                if (in_array($key, $saveArrayAsString) && $key !== 0) {
                    return 'isMultipleIdsInstance';
                } else {
                    return 'isSimpleInstance';
                }
            } elseif (is_object($oldValue) && is_callable($oldValue, 'getData') &&
                is_object($newValue) && is_callable($newValue, 'getData')) {
                return 'recursiveDataCall';
            } elseif (!is_object($oldValue) && !is_object($newValue)
                && $oldValue != $newValue
                && $newValue !== false
            ) {
                return 'notDeleted';
            }
        }
        return 'notLogged';
    }

    protected function innerConditionResolver($v, $k, $keysNotForLogging, $newValue)
    {
        if (!in_array($k, $keysNotForLogging) || is_int($k)) {
            if (is_object($v) && is_callable([$v, 'getData'], true)) {
                if (array_key_exists($k, $newValue)
                    && is_callable([$newValue[$k], 'getData'], true)
                ) {
                    return 'recursiveDataCall';
                }
            } elseif (is_array($newValue)) {
                if (array_key_exists($k, $newValue)) {
                    return 'recursiveCallForArray';
                }
            } else {
                return 'recursiveCallForString';
            }
        }
        return 'notLogged';
    }

    protected function _isConfig($logModel)
    {
        $isConfig = false;

        if ($logModel->getCategory() == 'admin/system_config') {
            $isConfig = true;
        }

        return $isConfig;
    }

    /**
     * Change keys for example store_id in cms pages
     * @param int $key
     * @param Log $category
     * @return int $key
     */
    protected function _changeNewKey($key, $category)
    {
        if ($key == 'store_id' && $category == 'cms/page') {
            $key = 'stores';
        }

        return $key;
    }

    protected function _prepareNewData($key, $value)
    {
        $keyNotForLogging = [
            'media_attributes',
            'media_gallery',
            'options',
            'product_options'
        ];

        if (in_array($key, $keyNotForLogging)) {
            $value = 'not logged now';
        }

        switch ($key) {
            case 'dob':
            case 'custom_theme_from':
            case 'custom_theme_to':
            case 'special_from_date':
            case 'special_to_date':
            case 'news_from_date':
            case 'custom_design_from':
                $value = date('Y-m-d', strtotime($value));
                break;
        }

        if (is_object($value)) {
            $value = get_class($value);
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                if (is_object($v)) {
                    $value[$k] = get_class($v);
                }
            }
            $value = $this->_prepareArrayOfValues($value);
        }

        if (is_bool($value)) {
            $value = (int)$value;
        }

        return $value;
    }

    protected function _prepareArrayOfValues($array)
    {
        $value = '';

        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                unset($array[$key]);
            }
        }

        if (is_array($value)) {
            try {
                $value = implode(',', $value);
            } catch (\Exception $e) {
                $value = 'array()';
            }
        }

        return $value;
    }

    protected function _prepareOldData($key, $value)
    {
        if ($key === 'qty') {
            $value = (int)$value;
        }

        if (is_array($value)) {
            $value = $this->_prepareArrayOfValues($value);
        }

        return $value;
    }
}
