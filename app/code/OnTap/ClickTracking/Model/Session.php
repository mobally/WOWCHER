<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ClickTracking\Model;

use Magento\Framework\Session\SessionManager;

class Session extends SessionManager
{
    /**
     * @param $key
     * @param $value
     */
    public function setTrackingValue($key, $value): void
    {
        if (!empty($value)) {
            $this->setData($key, $value);
        }
    }

    /**
     * @return mixed|null
     */
    public function getTrackingValue($key)
    {
        return $this->getData($key);
    }
}
