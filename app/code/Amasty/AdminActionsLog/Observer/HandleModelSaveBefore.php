<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_AdminActionsLog
 */


namespace Amasty\AdminActionsLog\Observer;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Backend\Customer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class HandleModelSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Amasty\AdminActionsLog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\AdminActionsLog\Helper\Data $helper,
        \Magento\Framework\App\State $appState
    ) {
        $this->objectManager = $objectManager;
        $this->registryManager = $coreRegistry;
        $this->helper = $helper;
        $this->appState = $appState;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                $object = $observer->getObject();

                if ($this->helper->needToSave($object)) {
                    $this->_saveOldData($object);
                }
            }
        } catch (LocalizedException $e) {
            null;// no action is Area Code in not set
        }
    }

    protected function _saveOldData($object)
    {
        if ($this->helper->needOldData($object)) {
            $isBundleProduct = $this->isBundleProduct($object);

            if ($this->_needLoadModel($object) || $isBundleProduct) {
                $class = get_class($object);
                $entity = $this->objectManager->get($class)->load($object->getId());
                $data = $this->getObjectData($entity);

                if ($isBundleProduct) {
                    if ($entity->getExtensionAttributes()->getBundleProductOptions()) {
                        $this->registryManager->register(
                            'amaudit_bundle_product_options_before',
                            $this->prepareBundleProductOptionsData(
                                $entity->getExtensionAttributes()->getBundleProductOptions()
                            ),
                            true
                        );
                    }
                }
            } else {
                $data = $this->getObjectData($object);
            }

            $this->registryManager->register('amaudit_data_before', $data, true);

            if ($object instanceof Product) {
                if ($object->getOptions()) {
                    $this->registryManager->register(
                        'amaudit_product_options_before',
                        $this->prepareProductOptionsDataAsArray($object->getOrigData('options')),
                        true
                    );
                }
            }
        }
    }

    private function getObjectData($object): ?array
    {
        if ($object instanceof Product) {
            $data = $this->helper->_prepareProductData($object);

            if ($object->getOptions()) {
                $this->registryManager->register(
                    'amaudit_product_options_before',
                    $this->prepareProductOptionsDataAsArray($object->getOrigData('options')),
                    true
                );
            }
        } else {
            $data = $object->getData();
        }

        return $data;
    }

    private function prepareProductOptionsDataAsArray(array $options): array
    {
        $optionsData = [];

        foreach ($options as $item) {
            $optionsData[$item->getId()] = $item->getData();

            if ($item->getValues()) {
                foreach ($item->getValues() as $value) {
                    $optionsData[$item->getId()]['values'][$value->getId()] = $value->getData();
                }
            }
        }

        return $optionsData;
    }

    private function prepareBundleProductOptionsData(array $options): array
    {
        $optionsData = [];

        foreach ($options as $option) {
            $optionsData[$option->getId()] = $option->getData();
        }

        return $optionsData;
    }

    protected function _needLoadModel($object)
    {
        $needLoadModel = false;

        $needLoadModelArray = [
            Customer::class
        ];

        foreach ($needLoadModelArray as $class) {
            if (is_a($object, $class)) {
                $needLoadModel = true;
            }
        }

        return $needLoadModel;
    }

    private function isBundleProduct($object): bool
    {
        return $object instanceof \Magento\Catalog\Model\Product
            && $object->getTypeId() === \Magento\Bundle\Model\Product\Type::TYPE_CODE;
    }
}
