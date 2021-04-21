<?php
namespace Rvs\VirtualProduct\Model\Voucher\Source;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{   
    const OPEN          = 0;
    const ASSIGNED      = 1; // Order # Assigned assigned
    const EMAIL_SENT    = 2; // Voucher assigned
    
    public function toOptionArray()
    {
        return [
            [
                'value' => self::OPEN,
                'label' => __('Available')
            ],
            [
                'value' => self::ASSIGNED,
                'label' => __('Assigned')
            ],
            [
                'value' => self::EMAIL_SENT,
                'label' => __('Email Sent')
            ],
        ];
    }

    public function toArray()
    {
        $_tmpOptions = $this->toOptionArray();
        $_options    = [];
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }

        return $_options;
    }
}
