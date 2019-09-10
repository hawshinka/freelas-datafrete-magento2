<?php

namespace H4w\Datafrete\Model\Config\Source;

class Displayorder implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Price')],
            ['value' => 1, 'label' => __('Deadline')],
        ];
    }

}