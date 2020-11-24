<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\Deal\Block;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Serializer implements ArgumentInterface
{
    /**
     * @var Json
     */
    protected Json $json;

    /**
     * Serializer constructor.
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function serialize(array $data)
    {
        return $this->json->serialize($data);
    }

    /**
     * @param string $data
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserialize(string $data)
    {
        try {
            return $this->json->unserialize($data);
        } catch (\Exception $e) {
            return false;
        }
    }
}
