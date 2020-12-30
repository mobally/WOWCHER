<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ProductImport\Model\Mapper;

class Grouped extends Product
{
    /**
     * @return array
     */
    protected function getProductMap()
    {
        return [
            'sku' => 'id',
            'name' => 'pageTitle',
            'description' => 'description',
            'short_description' => 'title',
//            'name' => 'emailSubject',
//            'description' => 'emailSubject',
//            'short_description' => 'emailSubject',
            'product_online' => 'product_online',
            'url_key' => ['id', function ($v, $data) {
                $urlPrefix = substr($data['urlPrefix'], 1);
                return $v . sprintf('-%s', $urlPrefix);
            }],
            'associated_skus' => ['products', function ($v, $data) {
                $skus = [];
                $defaultQty = count($v) === 1 ? '1.0000' : '0.0000';
                foreach ($v as $product) {
                    $skus[] = sprintf('%s=%s', $product['id'], $defaultQty);
                }
                return implode(',', $skus);
            }],
            'highlights' => ['highlights', function ($v, $data) {
                return $this->json->serialize($v);
            }],
            'terms' => ['terms', function ($v, $data) {
                return $this->json->serialize($v);
            }],
            'base_image' => ['images', [$this, 'createMainImageUrl']],
            'small_image' => ['images', [$this, 'createMainImageUrl']],
            'thumbnail_image' => ['images', [$this, 'createMainImageUrl']],
            'additional_images' => ['images', function ($images, $data) {
                if (!$data['is_new']) {
                    return null;
                }
                array_shift($images);
                $imagesOut = [];
                foreach ($images as $image) {
                    $imagesOut[] = sprintf('%s.%s', $image['imageUrl'], $image['extension']);
                }
                return implode(',', $imagesOut);
            }],
            'categories' => ['category', function ($v, $data) {
                $level1 = $data['category']['name'];
                $level2 = $data['subCategory']['name'];
                return sprintf('Shop/%s/%s', $level1, $level2);
            }],
            'productdisplay_row' => ['productDisplay', function ($v, $data) {
                if (empty($v['row'])) {
                    return null;
                }
                return $this->json->serialize($v['row']);
            }],
            'productdisplay_column' => ['productDisplay', function ($v, $data) {
                if (empty($v['column'])) {
                    return null;
                }
                return $this->json->serialize($v['column']);
            }],
            'productdisplay_type' => ['productDisplay', function ($v, $data) {
                return $v['type'];
            }],
            'business_id' => ['business', function ($v, $data) {
                return $v['id'];
            }],
            'business_image_url' => ['business', function ($v, $data) {
                if (isset($v['image']) && isset($v['image']['imageUrl'])) {
                    return sprintf('%s-logo.%s', $v['image']['imageUrl'], $v['image']['extension']);
                }
                return null;
            }],
            'display_bought' => ['display', function ($v, $data) {
                return $v['bought'] ? "yes" : "no";
            }],
            'total_bought' => 'totalBought',
            'web_address' => ['id', function ($v, $data) {
                return isset($data['webAddress']) ? $data['webAddress'] : null;
            }],
            'business_image_alt' => ['business', function ($v, $data) {
                if (isset($v['image']) && isset($v['image']['alt'])) {
                    return $v['image']['alt'];
                }
                return null;
            }],
            'display_business' => ['display', function ($v, $data) {
                return $v['business'] ? "yes" : "no";
            }],
            'display_price_text' => ['display', function ($v, $data) {
                return $v['priceText'] ? "yes" : "no";
            }],
            'display_discount' => ['display', function ($v, $data) {
                return $v['discount'] ? "yes" : "no";
            }],
            'price_text' => 'priceText',
            'deal_position' => ['orderWeight', function ($v, $data) {
                return floatval($v) * 100000;
            }],
            'product_websites' => ['scheduledLocations', [$this, 'getWebsites']],
            'ware_house_deal'=>['display', function ($v, $data) {
                return $v['warehouseDeal'] ? "yes" : "no";
            }],
        ];
    }

    /**
     * @return array
     */
    protected function getProductDefaults()
    {
        return [
            'visibility' => 'Catalog, Search',
            'attribute_set_code' => 'Default',
            'weight' => '1',
            'tax_class_name' => '',
            'product_type' => 'grouped',
            'price' => '',
            'special_price' => '',
        ];
    }
}
