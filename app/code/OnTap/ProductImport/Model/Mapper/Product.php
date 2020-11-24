<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ProductImport\Model\Mapper;

class Product
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * Product constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(\Magento\Framework\Serialize\Serializer\Json $json)
    {
        $this->json = $json;
    }

    /**
     * All columns and their order
     *
     * WARNING: If column not listed here.. it will not be imported
     *
     * @return string[]
     */
    public static function getColumnNames(): array
    {
        return [
            'sku',
            'name',
            'url_key',
            'price',
            'special_price',
            'visibility',
            'attribute_set_code',
            'product_websites',
            'weight',
            'product_online',
            'tax_class_name',
            'product_type',
            'associated_skus', // grouped product associations
            'highlights',
            'terms',
            'description',
            'short_description',
            'base_image',
            'small_image',
            'thumbnail_image',
            'additional_images',
            'categories',
            'productdisplay_column',
            'productdisplay_row',
            'productdisplay_type',
            'rowid',
            'columnid',
            'business_id',
            'business_image_url',
            'display_bought',
            'total_bought',
            'display_business',
            'business_image_alt',
            'web_address',
            'display_price_text',
            'display_discount',
            'price_text',
            'deal_position',
            'product_postage_price',
        ];
    }

    /**
     * @return array
     */
    protected function getProductMap()
    {
        return [
            'sku' => 'id',
            'name' => 'friendlyName',
            'url_key' => ['id', function ($v, $data) {
                $urlPrefix = substr($data['deal']['urlPrefix'], 1);
                return $v . sprintf('-%s', $urlPrefix);
            }],
            'price' => 'originalPrice',
            'special_price' => 'price',
            'rowid' => 'rowId',
            'columnid' => 'columnId',
            'product_online' => ['deal', function ($v, $data) {
                return $v['product_online'];
            }],
            'product_websites' => ['deal', [$this, 'getWebsites']],
            'deal_position' => ['deal', function ($v, $data) {
                return floatval($v['orderWeight']) * 100000;
            }],
            'product_postage_price' => 'postagePrice',
        ];
    }

    /**
     * @return string[]
     */
    protected function getProductDefaults()
    {
        return [
            'visibility' => 'Not Visible Individually',
            'attribute_set_code' => 'Default',
            'weight' => '1',
            'tax_class_name' => 'Taxable Goods',
            'product_type' => 'simple',
            'associated_skus' => '',
        ];
    }

    /**
     * @param array $locations
     * @param array $data
     * @return string
     */
    protected function getWebsites($locations, $data)
    {
        if (isset($locations['scheduledLocations'])) {
            $locations = $locations['scheduledLocations'];
        }

        $sites = ['uk', ];
        foreach ($locations as $location) {
            switch ($location['location']) {
                case 'spain':
                    $sites[] = 'es';
                    break;
                case 'belgium':
                    $sites[] = 'be';
                    break;
                case 'poland':
                    $sites[] = 'pl';
                    break;
            }
        }
        return implode(',', $sites);
    }

    /**
     * @param array $images
     * @param array $data
     * @return string
     */
    protected function createMainImageUrl($images, $data)
    {
        if (isset($images[0]) && $data['is_new']) {
            $image = $images[0];
            return sprintf('%s.%s', $image['imageUrl'], $image['extension']);
        }
        return '';
    }

    /**
     * @param array $from
     * @param array $to
     * @return array
     */
    public function _map(array $from, array &$to)
    {
        foreach ($this->getProductMap() as $toField => $fromField) {
            if (
                is_array(
                    $fromField
                ) && isset(
                    $fromField[0]
                ) && isset(
                    $fromField[1]
                ) && is_callable(
                    $fromField[1]
                )
            ) {
                $call = $fromField[1];
                $to[$toField] = $call($from[$fromField[0]], $from);
            } else if (is_array($fromField)) {
                throw new \Exception(sprintf('"%s::%s" is not callable', get_class($fromField[1][0]), $fromField[1][1]));
            } else {
                $to[$toField] = $from[$fromField];
            }

        }

        foreach ($this->getProductDefaults() as $keyTo => $value) {
            if (!isset($to[$keyTo])) {
                $to[$keyTo] = $value;
            }
        }

        $cleanedRow = [];
        foreach (self::getColumnNames() as $columnName) {
            $cleanedRow[$columnName] = isset($to[$columnName]) ? $to[$columnName] : null;
        }

        return $cleanedRow;
    }
}
